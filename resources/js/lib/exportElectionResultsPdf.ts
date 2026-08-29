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
export function formatDate(dateStr: string | Date): string {
    const date = typeof dateStr === 'string' ? new Date(dateStr) : dateStr;
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

            if (!ctx) return reject(new Error('Failed to get 2D context'));

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

        img.onerror = reject;
        img.src = url;
    });
};

// DRY Wrapper for safe image loading without repetitive try-catch blocks
const safeLoadImage = async (url: string | null | undefined, crop: boolean = false): Promise<string | null> => {
    if (!url) return null;
    try {
        return await getBase64ImageFromURL(url, crop);
    } catch (error) {
        console.warn(`Could not load image at ${url}`, error);
        return null;
    }
};

/* PDF EXPORT FOR ELECTION RESULTS */

export async function exportElectionResultsPdf(
    election: Election, 
    positions: Record<string, CandidateResult[]>,
    signatureUrl?: string | null,
    userName: string = 'ADMINISTRATOR',
    returnAsBlob: boolean = false
): Promise<Blob | void> { 
    const doc = new jsPDF({ orientation: 'portrait' });
    const pageHeight = doc.internal.pageSize.getHeight(); 

    // 1. ASSET LOADING (DRY Promise.all array)
    const [leftLogo, rightLogo, signatureBase64] = await Promise.all([
        safeLoadImage('/images/logo/ausl.png'),
        safeLoadImage('/images/logo/comelec-logo.jpg', true),
        safeLoadImage(signatureUrl)
    ]);

    // 2. HEADER
    if (leftLogo) doc.addImage(leftLogo, 'PNG', 20, 8, 22, 22);
    if (rightLogo) doc.addImage(rightLogo, 'PNG', 160, 8, 22, 22);

    // DRY Helper for centered text
    const addCenterText = (text: string, y: number, size: number, weight: 'normal' | 'bold' = 'normal') => {
        doc.setFont('helvetica', weight);
        doc.setFontSize(size);
        doc.text(text, 105, y, { align: 'center' });
    };

    addCenterText('ARELLANO LAW FOUNDATION', 14, 12, 'bold');
    addCenterText('Taft Ave, Cor. Menlo St. Pasay City · Tel. No. 404-3089 to 93', 19, 9);
    addCenterText('ELECTION RESULTS', 28, 14, 'bold');
    addCenterText(election.name, 34, 11);
    addCenterText(`${formatDate(election.start_date)} - ${formatDate(election.end_date)}`, 39, 10);

    let startY = 48;

    // 3. TABLES
    for (const [positionName, candidates] of Object.entries(positions)) {
        let totalVotes = 0;
        
        // DRY: Sort and accumulate totals simultaneously
        const tableData: any[] = [...candidates]
            .sort((a, b) => b.votes - a.votes)
            .map((c, idx) => {
                totalVotes += c.votes;
                return [
                    (idx + 1).toString(),
                    c.candidate_name,
                    c.candidate_party || 'Independent',
                    c.votes.toString()
                ];
            });

        tableData.push(['', '', 'TOTAL VOTES', totalVotes.toString()]);

        const estimatedTableHeight = (tableData.length + 1) * 9; 
        if (startY > 30 && startY + estimatedTableHeight + 15 > pageHeight - 40) {
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
            didParseCell: (data) => {
                if (data.section === 'head' && data.column.index === 3) {
                    data.cell.styles.halign = 'right';
                }
            },
            willDrawCell: (data) => {
                // Style the 'TOTAL VOTES' row dynamically
                if (data.row.index === tableData.length - 1) {
                    doc.setFont('helvetica', 'bold');
                    data.cell.styles.fontStyle = 'bold';
                    data.cell.styles.textColor = [0, 0, 0];
                }
            },
        });

        startY = (doc as any).lastAutoTable.finalY + 15;
    }

    // 4. FOOTER (Signatures)
    const bottomMargin = 40; 
    if (startY > pageHeight - bottomMargin - 10) doc.addPage();

    const rightX = 150;
    const footerStartY = pageHeight - bottomMargin;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text('Prepared by:', rightX, footerStartY);

    if (signatureBase64) {
        doc.addImage(signatureBase64, 'PNG', rightX + 3, footerStartY + 1, 30, 13);
    }

    doc.setFont('helvetica', 'bold');
    doc.text(userName.toUpperCase(), rightX + 23, footerStartY + 20, { align: 'center' });

    // 5. GLOBAL PAGINATION & TIMESTAMPS
    const pageCount = (doc as any).internal.getNumberOfPages();
    const now = new Date();
    const timestampText = `System Report Generated on: ${formatDate(now)} at ${now.toLocaleTimeString('en-US', {
        hour: '2-digit', minute: '2-digit'
    })}`;
    
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i); 
        
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(128, 128, 128); 
        doc.setFontSize(8);
        
        // Bottom Left: Timestamp
        doc.text(timestampText, 14, pageHeight - 10);
        // Bottom Right: Pagination
        doc.text(`Page ${i} of ${pageCount}`, 196, pageHeight - 10, { align: 'right' });
    }

    // 6. EXPORT
    if (returnAsBlob) {
        return doc.output('blob');
    }

    doc.save(`${election.name.replace(/\s+/g, ' ')}-RESULTS.pdf`);
}