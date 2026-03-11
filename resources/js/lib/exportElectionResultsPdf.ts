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

// Helper function to convert an image URL to Base64 for jsPDF, with optional circular crop
const getBase64ImageFromURL = (url: string, cropCircle: boolean = false): Promise<string> => {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.setAttribute("crossOrigin", "anonymous");
    
    img.onload = () => {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      
      if (!ctx) {
        reject(new Error("Failed to get 2D context"));
        return;
      }

      if (cropCircle) {
        // Find the smallest dimension to make a perfect square canvas
        const size = Math.min(img.width, img.height);
        canvas.width = size;
        canvas.height = size;

        // Create a circular clipping path
        ctx.beginPath();
        ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2, true);
        ctx.closePath();
        ctx.clip();

        // Center the image if it is rectangular
        const dx = (size - img.width) / 2;
        const dy = (size - img.height) / 2;
        ctx.drawImage(img, dx, dy, img.width, img.height);
      } else {
        // Default behavior for uncropped images
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
      }

      // Export as PNG to preserve the transparent background outside the circle
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
  // ==================== LOGOS ====================
  try {
    const leftLogoUrl = '/images/logo/ausl.png'; 
    const rightLogoUrl = '/images/logo/comelec-logo.jpg'; 
    
    // Pass `true` to the right logo to trigger the circular crop
    const [leftLogoBase64, rightLogoBase64] = await Promise.all([
      getBase64ImageFromURL(leftLogoUrl),
      getBase64ImageFromURL(rightLogoUrl, true) 
    ]);
    
    // Add to Document: addImage(imageData, format, X, Y, Width, Height)
    doc.addImage(leftLogoBase64, 'PNG', 20, 8, 22, 22); 
    doc.addImage(rightLogoBase64, 'PNG', 160, 8, 22, 22); 

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


      didParseCell: function(data) {
        // Check if we are in the header row and specifically the 4th column (index 3)
        if (data.section === 'head' && data.column.index === 3) {
          data.cell.styles.halign = 'right'; 
        }
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