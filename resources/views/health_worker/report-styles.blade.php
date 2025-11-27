<style>
    /* Vaccination Report Table Styles */
    #vaccinationReportTable {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
        background-color: #ffffff;
    }
    
    /* Table headers */
    #vaccinationReportTable thead th {
        background-color: #E5E7EB;
        color: #111827;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        padding: 12px 8px;
        border: 1px solid #9CA3AF;
        white-space: normal;
        word-wrap: break-word;
        font-size: 13px;
    }
    
    /* Sub-header row */
    #vaccinationReportTable thead tr:nth-child(2) th {
        background-color: #F3F4F6;
        font-size: 13px;
        padding: 10px 6px;
        font-weight: 700;
    }
    
    /* Sticky first column (Area) */
    #vaccinationReportTable th:first-child,
    #vaccinationReportTable td:first-child {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #E5E7EB;
        font-weight: 600;
        min-width: 140px;
        max-width: 180px;
        font-size: 14px;
    }
    
    #vaccinationReportTable tbody td:first-child {
        background-color: #FFFFFF;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    
    /* TOTAL row sticky background */
    #vaccinationReportTable tbody tr:last-child td:first-child {
        background-color: #FEF3C7;
    }
    
    /* Table body cells */
    #vaccinationReportTable tbody td {
        padding: 10px 6px;
        border: 1px solid #D1D5DB;
        text-align: center;
        color: #374151;
        font-size: 14px;
    }
    
    /* TOTAL row styling */
    #vaccinationReportTable tbody tr:last-child {
        background-color: #FEF3C7;
        font-weight: 700;
    }
    
    #vaccinationReportTable tbody tr:last-child td {
        border-top: 2px solid #92400E;
        border-bottom: 2px solid #92400E;
    }
    
    /* Hover effect for non-TOTAL rows */
    #vaccinationReportTable tbody tr:not(:last-child):hover {
        background-color: #F9FAFB;
    }
    
    /* Column widths */
    #vaccinationReportTable th:nth-child(2),
    #vaccinationReportTable td:nth-child(2) {
        min-width: 80px;
        max-width: 100px;
    }
    
    /* Vaccine data columns (M/F/T/%) */
    #vaccinationReportTable th:not(:first-child):not(:nth-child(2)),
    #vaccinationReportTable td:not(:first-child):not(:nth-child(2)) {
        min-width: 55px;
        max-width: 70px;
    }
    
    /* Bold total count columns */
    #vaccinationReportTable tbody td:nth-child(4n+4) {
        font-weight: 600;
        color: #1F2937;
        font-size: 15px;
    }
    
    /* Scrollbar styling */
    .overflow-x-auto::-webkit-scrollbar {
        height: 10px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #F3F4F6;
        border-radius: 5px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #9CA3AF;
        border-radius: 5px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #6B7280;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        #vaccinationReportTable {
            font-size: 12px;
        }
        
        #vaccinationReportTable thead th,
        #vaccinationReportTable tbody td {
            padding: 6px 4px;
        }
        
        #vaccinationReportTable th:first-child,
        #vaccinationReportTable td:first-child {
            min-width: 110px;
            font-size: 12px;
        }
        
        #vaccinationReportTable th:not(:first-child):not(:nth-child(2)),
        #vaccinationReportTable td:not(:first-child):not(:nth-child(2)) {
            min-width: 45px;
            max-width: 55px;
        }
        
        #vaccinationReportTable tbody td:nth-child(4n+4) {
            font-size: 13px;
        }
    }
    
    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        #vaccinationReportTable {
            font-size: 8px;
        }
        
        #vaccinationReportTable th:first-child,
        #vaccinationReportTable td:first-child {
            position: static;
            box-shadow: none;
        }
    }
</style>
