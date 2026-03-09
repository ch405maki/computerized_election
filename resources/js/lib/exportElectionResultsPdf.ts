import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

/* =========================================================
   TYPES
========================================================= */

export interface CandidateResult {
  id: number;
  candidate_name: string;
  candidate_party: string | null;
  candidate_picture: string | null;
  votes: number;
}

export interface Election {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
}

/* =========================================================
   UTILS
========================================================= */

export function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

// Helper function to convert an image URL to Base64 for jsPDF
const getBase64ImageFromURL = (url: string): Promise<string> => {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.setAttribute("crossOrigin", "anonymous");
    img.onload = () => {
      const canvas = document.createElement("canvas");
      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext("2d");
      ctx?.drawImage(img, 0, 0);
      resolve(canvas.toDataURL("image/png"));
    };
    img.onerror = (error) => reject(error);
    img.src = url;
  });
};

/* =========================================================
   PDF EXPORT FOR ELECTION RESULTS
========================================================= */

export async function exportElectionResultsPdf(
  election: Election, 
  positions: Record<string, CandidateResult[]>
) {
  const doc = new jsPDF({ orientation: "portrait" });
  
  // ==================== LOGOS ====================
  try {
    // 1. Define paths to your two logos in the public folder
    const leftLogoUrl = '/images/logo/ausl.png';  // Update this path
    const rightLogoUrl = '/images/logo/au-logo.png'; // Update this path
    
    // 2. Load both images at the same time for performance
    const [leftLogoBase64, rightLogoBase64] = await Promise.all([
      getBase64ImageFromURL(leftLogoUrl),
      getBase64ImageFromURL(rightLogoUrl)
    ]);
    
    // 3. Add to Document: addImage(imageData, format, X, Y, Width, Height)
    
    // Left Logo
    doc.addImage(leftLogoBase64, 'PNG', 20, 8, 22, 22); 
    
    // Right Logo (210 page width - 20 right margin - 22 image width = X coordinate of 168)
    doc.addImage(rightLogoBase64, 'PNG', 168, 8, 22, 22); 

  } catch (error) {
    console.warn("Could not load one or both logos. Generating PDF without them.", error);
  }

  // ==================== HEADER ====================
  doc.setFont("helvetica", "bold");
  doc.setFontSize(12);
  doc.text("ARELLANO LAW FOUNDATION", 105, 14, { align: "center" });

  doc.setFont("helvetica", "normal");
  doc.setFontSize(9);
  doc.text(
    "Taft Ave, Cor. Menlo St. Pasay City · Tel. No. 404-3089 to 93",
    105,
    19,
    { align: "center" }
  );

  doc.setFont("helvetica", "bold");
  doc.setFontSize(14);
  doc.text("ELECTION RESULTS", 105, 28, { align: "center" });
  
  doc.setFontSize(11);
  doc.text(election.name, 105, 34, { align: "center" });
  
  doc.setFont("helvetica", "normal");
  doc.setFontSize(10);
  const dateRange = `${formatDate(election.start_date)} - ${formatDate(election.end_date)}`;
  doc.text(dateRange, 105, 39, { align: "center" });

  let startY = 48;

  // ==================== TABLES ====================
  for (const [positionName, candidates] of Object.entries(positions)) {
    const sortedCandidates = [...candidates].sort((a, b) => b.votes - a.votes);
    const totalVotes = sortedCandidates.reduce((sum, c) => sum + c.votes, 0);

    doc.setFont("helvetica", "bold");
    doc.setFontSize(11);
    doc.text(positionName, 14, startY);
    
    doc.setLineWidth(0.5);
    doc.line(14, startY + 1, 196, startY + 1);

    const tableData: any[] = sortedCandidates.map((c, idx) => [
      (idx + 1).toString(),
      c.candidate_name,
      c.candidate_party || "Independent",
      c.votes.toString()
    ]);

    tableData.push([ "", "", "TOTAL VOTES", totalVotes.toString() ]);

    autoTable(doc, {
      startY: startY + 4,
      head: [["Rank", "Candidate Name", "Party", "Votes"]],
      body: tableData,
      theme: "plain",
      styles: { fontSize: 10, cellPadding: 2 },
      headStyles: { fontStyle: "normal", textColor: [100, 100, 100] },
      columnStyles: {
        0: { halign: "center", cellWidth: 20 },
        3: { halign: "right", cellWidth: 30 }
      },
      willDrawCell: function (data) {
        if (data.row.index === tableData.length - 1) {
          doc.setFont("helvetica", "bold");
          data.cell.styles.fontStyle = "bold";
          data.cell.styles.textColor = [0, 0, 0];
        }
      }
    });

    startY = (doc as any).lastAutoTable.finalY + 15;
  }

  // ==================== FOOTER ====================
  if (startY > 240) {
    doc.addPage();
    startY = 20;
  }

  doc.setFont("helvetica", "normal");
  doc.setFontSize(10);
  doc.text("Prepared by:", 14, startY + 10);

  doc.setLineWidth(0.5);
  doc.line(14, startY + 30, 60, startY + 30);
  doc.setFont("helvetica", "bold");
  doc.text("ADMINISTRATOR", 14, startY + 35);

  const safeFilename = `${election.name.replace(/\s+/g, "_")}_Results.pdf`;
  doc.save(safeFilename);
}