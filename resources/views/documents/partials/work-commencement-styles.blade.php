<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        height: 100%;
    }

    body {
        font-family: 'Tajawal', sans-serif;
        font-size: 12px;
        line-height: 1.4;
        color: #333;
        background: #fff;
        direction: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }};
    }

    .page {
        width: 100%;
        height: 100%;
        margin: 0;
        background: white;
        padding: 0;
        position: relative;
    }

    /* Content wrapper with minimal margins for single page */
    .content {
        padding: 5mm;
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        min-height: calc(100vh - 10mm);
    }

    /* Header - spans both columns - compact */
    .header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2px 2px 5px 2px;
        border-bottom: 2px solid #2563eb;
        padding-bottom: 4px;
        grid-column: 1 / -1;
        z-index: 2;
        position: relative;
    }

    .company-info {
        text-align: center;
        flex: 1;
        z-index: 2;
        position: relative;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
        color: #1e40af;
        margin-bottom: 2px;
        z-index: 2;
        position: relative;
    }

    .company-name-ar {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 4px;
        z-index: 2;
        position: relative;
    }

    /* Document Title - spans both columns - compact */
    .document-title {
        text-align: center;
        margin: 2px 2px 6px 2px;
        background: #f8fafc;
        padding: 5px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        grid-column: 1 / -1;
        z-index: 2;
        position: relative;
    }

    .document-title h1 {
        font-size: 18px;
        font-weight: bold;
        color: #1e40af;
        margin-bottom: 3px;
        z-index: 2;
        position: relative;
    }

    .document-title h2 {
        font-size: 16px;
        color: #64748b;
        margin-bottom: 4px;
        z-index: 2;
        position: relative;
    }

    .document-number {
        font-size: 12px;
        color: #475569;
        margin-top: 3px;
        z-index: 2;
        position: relative;
    }

    /* Content Sections - compact */
    .content-section {
        margin: 3px 0;
        /* background: #fff; */
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        height: fit-content;
        z-index: 2;
        position: relative;
    }

    .section-header {
        /* background: #f1f5f9; */
        padding: 4px 8px;
        font-weight: bold;
        font-size: 12px;
        color: #1e40af;
        border-bottom: 1px solid #e2e8f0;
        z-index: 2;
        position: relative;
    }

    .section-content {
        padding: 6px;
        z-index: 2;
        position: relative;
    }

    /* Declaration Text - compact */
    .declaration-text {
        font-size: 12px;
        line-height: 1.3;
        text-align: justify;
        margin-bottom: 6px;
        z-index: 2;
        position: relative;
    }

    .declaration-text p {
        margin-bottom: 4px;
        z-index: 2;
        position: relative;
    }

    /* Info Fields - compact */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
        margin-bottom: 5px;
        z-index: 2;
        position: relative;
    }

    .info-field {
        display: flex;
        align-items: center;
        padding: 2px 0;
        z-index: 2;
        position: relative;
    }

    .field-label {
        font-weight: bold;
        color: #475569;
        min-width: 85px;
        font-size: 10px;
        {{ app()->getLocale() == 'ar' || app()->getLocale() == 'ku' ? 'margin-left: 6px;' : 'margin-right: 6px;' }}
        z-index: 2;
        position: relative;
    }

    .field-value {
        color: #1e293b;
        flex: 1;
        padding: 2px 4px;
        background: #f8fafc;
        border-radius: 2px;
        font-size: 10px;
        z-index: 2;
        position: relative;
    }

    /* Employee Info Grid - compact */
    .employee-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
        margin-bottom: 5px;
        z-index: 2;
        position: relative;
    }

    .manual-entry-field {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        padding: 4px 0;
        z-index: 2;
        position: relative;
    }

    .manual-entry-label {
        font-weight: bold;
        color: #475569;
        font-size: 11px;
        min-width: 80px;
        margin-right: 8px;
        z-index: 2;
        position: relative;
    }

    .manual-entry-line {
        flex: 1;
        border-bottom: 2px solid #64748b;
        min-height: 20px;
        margin-left: 5px;
        z-index: 2;
        position: relative;
    }

    /* Column layout helpers */
    .left-column {
        display: flex;
        flex-direction: column;
        z-index: 2;
        position: relative;
    }

    .right-column {
        display: flex;
        flex-direction: column;
        z-index: 2;
        position: relative;
    }

    /* Signature Section - spans both columns - compact */
    .signature-section {
        margin: 8px 0 2px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        grid-column: 1 / -1;
        z-index: 2;
        position: relative;
    }

    .signature-box {
        text-align: center;
        padding: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background: #fafafa;
        z-index: 2;
        position: relative;
    }

    .signature-box h3 {
        font-size: 12px;
        font-weight: bold;
        color: #1e40af;
        margin-bottom: 15px;
        z-index: 2;
        position: relative;
    }

    .signature-line {
        border-top: 2px solid #64748b;
        margin: 15px auto;
        width: 120px;
        min-height: 2px;
        z-index: 2;
        position: relative;
    }

    .signature-date {
        margin-top: 6px;
        font-size: 10px;
        color: #64748b;
        z-index: 2;
        position: relative;
    }

    /* Field line for manual entry */
    .field-line {
        border-bottom: 1px solid #64748b33;
        display: inline-block;
        min-width: 200px;
        min-height: 22px;
        margin: 0 5px;
        vertical-align: bottom;
        z-index: 2;
        position: relative;
    }

    /* Date line for signature dates */
    .date-line {
        border-bottom: 2px solid #64748b;
        display: inline-block;
        width: 120px;
        min-height: 20px;
        margin: 0 5px;
        vertical-align: bottom;
        z-index: 2;
        position: relative;
    }

    /* Name line for company representative */
    .name-line {
        border-bottom: 2px solid #64748b;
        display: block;
        width: 80%;
        min-height: 22px;
        margin: 12px auto;
        z-index: 2;
        position: relative;
    }

    /* Footer - compact */
    .footer {
        position: fixed;
        bottom: 5mm;
        left: 5mm;
        right: 5mm;
        text-align: center;
        font-size: 8px;
        color: #64748b;
        border-top: 1px solid #e2e8f0;
        padding-top: 3px;
        z-index: 2;
        position: relative;
    }

    /* Action Buttons */
    .actions-container {
        position: fixed;
        top: 20px;
        {{ app()->getLocale() == 'ar' || app()->getLocale() == 'ku' ? 'left: 20px;' : 'right: 20px;' }}
        z-index: 1000;
        display: flex;
        gap: 10px;
    }

    .action-button {
        padding: 10px 20px;
        background: #2563eb;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .action-button:hover {
        background: #1d4ed8;
    }

    .print-button {
        background: #059669;
    }

    .print-button:hover {
        background: #047857;
    }

    /* Print Styles - adjusted for single page */
    @media print {
        .actions-container {
            display: none !important;
        }
        
        .page {
            margin: 0;
            box-shadow: none;
        }

        .content {
            padding: 5mm;
        }
        
        .footer {
            position: fixed;
            bottom: 3mm;
        }
    }

    .no-print {
        display: {{ isset($is_pdf) ? 'none' : 'block' }} !important;
    }

    /* RTL Support */
    .rtl-content {
        text-align: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'ku' ? 'right' : 'left' }};
    }

    .number-field {
        direction: ltr;
        text-align: left;
    }
</style>