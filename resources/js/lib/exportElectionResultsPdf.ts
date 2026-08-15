import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

/* TYPES */
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

/* UTILS */
export function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

// Helper function to convert an image URL to Base64 for jsPDF, with optional circular crop
const getBase64ImageFromURL = (url: string, cropCircle: boolean = false): Promise<string> => {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.setAttribute('crossOrigin', 'anonymous');

        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            if (!ctx) {
                reject(new Error('Failed to get 2D context'));
                return;
            }

            if (cropCircle) {
                const size = Math.min(img.width, img.height);
                canvas.width = size;
                canvas.height = size;

                ctx.beginPath();
                ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2, true);
                ctx.closePath();
                ctx.clip();

                const dx = (size - img.width) / 2;
                const dy = (size - img.height) / 2;
                ctx.drawImage(img, dx, dy, img.width, img.height);
            } else {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
            }

            resolve(canvas.toDataURL('image/png'));
        };

        img.onerror = (error) => reject(error);
        img.src = url;
    });
};

/* PDF EXPORT FOR ELECTION RESULTS */

// NEW: Added signatureUrl and userName parameters
export async function exportElectionResultsPdf(
    election: Election, 
    positions: Record<string, CandidateResult[]>,
    signatureUrl?: string | null,
    userName: string = 'ADMINISTRATOR' 
) {
    const doc = new jsPDF({ orientation: 'portrait' });
    const pageHeight = doc.internal.pageSize.getHeight(); 

    // LOGOS & SIGNATURE FETCHING
    let leftLogoBase64, rightLogoBase64, signatureBase64;
    try {
        const [left, right] = await Promise.all([
            getBase64ImageFromURL('/images/logo/ausl.png'),
            getBase64ImageFromURL('/images/logo/comelec-logo.jpg', true)
        ]);
        leftLogoBase64 = left;
        rightLogoBase64 = right;
    } catch (error) {
        console.warn('Could not load one or both logos.', error);
    }

    // Fetch the signature independently so if it fails, the PDF still generates
    if (signatureUrl) {
        try {
            signatureBase64 = await getBase64ImageFromURL(signatureUrl, false);
        } catch (error) {
            console.warn('Could not load user signature.', error);
        }
    }

    // Add Logos to Document
    if (leftLogoBase64) doc.addImage(leftLogoBase64, 'PNG', 20, 8, 22, 22);
    if (rightLogoBase64) doc.addImage(rightLogoBase64, 'PNG', 160, 8, 22, 22);

    // HEADER
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('ARELLANO LAW FOUNDATION', 105, 14, { align: 'center' });

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.text('Taft Ave, Cor. Menlo St. Pasay City · Tel. No. 404-3089 to 93', 105, 19, { align: 'center' });

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text('ELECTION RESULTS', 105, 28, { align: 'center' });

    doc.setFontSize(11);
    doc.text(election.name, 105, 34, { align: 'center' });

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    const dateRange = `${formatDate(election.start_date)} - ${formatDate(election.end_date)}`;
    doc.text(dateRange, 105, 39, { align: 'center' });

    let startY = 48;

    // TABLES
    for (const [positionName, candidates] of Object.entries(positions)) {
        const sortedCandidates = [...candidates].sort((a, b) => b.votes - a.votes);
        const totalVotes = sortedCandidates.reduce((sum, c) => sum + c.votes, 0);

        const tableData: any[] = sortedCandidates.map((c, idx) => [
            (idx + 1).toString(),
            c.candidate_name,
            c.candidate_party || 'Independent',
            c.votes.toString(),
        ]);

        tableData.push(['', '', 'TOTAL VOTES', totalVotes.toString()]);

        const estimatedRowHeight = 9;
        const estimatedTableHeight = (tableData.length + 1) * estimatedRowHeight;
        const spaceNeeded = estimatedTableHeight + 15; 

        if (startY > 30 && startY + spaceNeeded > pageHeight - 20) {
            doc.addPage();
            startY = 20; 
        }

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.text(positionName, 14, startY);

        doc.setLineWidth(0.5);
        doc.line(14, startY + 1, 196, startY + 1);

        autoTable(doc, {
            startY: startY + 4,
            head: [['Rank', 'Candidate Name', 'Party', 'Votes']],
            body: tableData,
            theme: 'plain',
            styles: { fontSize: 10, cellPadding: 2 },
            headStyles: { fontStyle: 'normal', textColor: [100, 100, 100] },
            columnStyles: {
                0: { halign: 'center', cellWidth: 20 },
                3: { halign: 'right', cellWidth: 30 },
            },
            didParseCell: function (data) {
                if (data.section === 'head' && data.column.index === 3) {
                    data.cell.styles.halign = 'right';
                }
            },
            willDrawCell: function (data) {
                if (data.row.index === tableData.length - 1) {
                    doc.setFont('helvetica', 'bold');
                    data.cell.styles.fontStyle = 'bold';
                    data.cell.styles.textColor = [0, 0, 0];
                }
            },
        });

        startY = (doc as any).lastAutoTable.finalY + 15;
    }

    // FOOTER
    if (startY > 240) {
        doc.addPage();
        startY = 20;
    }

    const rightX = 150;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text('Prepared by:', rightX, startY + 10);

    // NEW: Render the signature image if it successfully loaded
    if (signatureBase64) {
        // Placed nicely in the gap between "Prepared by:" and the drawn line
        doc.addImage(signatureBase64, 'PNG', rightX + 3, startY + 11, 40, 18);
    }

    doc.setFont('helvetica', 'bold');
    
    // NEW: Replaced hardcoded 'ADMINISTRATOR' with dynamically centered user name
    const finalName = userName.toUpperCase();
    doc.text(finalName, rightX + 23, startY + 35, { align: 'center' });

    const safeFilename = `${election.name.replace(/\s+/g, ' ')}-RESULTS.pdf`;
    doc.save(safeFilename);
}