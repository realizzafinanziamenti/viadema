<?php

return [
    // Both CRM imports read 100 rows per chunk. Bound the complete workbook chain.
    'max_chunks' => (int) env('EXCEL_IMPORT_MAX_CHUNKS', 100),
    'max_xml_bytes' => (int) env('EXCEL_IMPORT_MAX_XML_BYTES', 16 * 1024 * 1024),
    'max_uncompressed_bytes' => (int) env('EXCEL_IMPORT_MAX_UNCOMPRESSED_BYTES', 64 * 1024 * 1024),
    'max_zip_entries' => (int) env('EXCEL_IMPORT_MAX_ZIP_ENTRIES', 2048),
];
