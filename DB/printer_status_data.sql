CREATE TABLE `printer_status_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'descripcion del status S1',
  `cashregister_user_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Número de Cajero asignado',
  `total_daily_invoices` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Total de ventas diarias',
  `last_invoice_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Número de la última factura',
  `daily_invoice_quantity` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cantidad de facturas emitidas en el día',
  `last_debitnote_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Número de la última nota de débito',
  `daily_debitnote_quantity` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cantidad de notas de débito emitidas en el día',
  `last_creditnote_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Número de la última nota de crédito',
  `daily_creditnote_quantity` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cantidad de notas de crédito emitidas en el día',
  `last_nonfiscal_document_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Número del último documento no fiscal',
  `daily_nonfiscal_documents_quantity` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cantidad de documentos no fiscales emitidos en el día',
  `daily_fiscal_memory_reports` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Contador de reportes de Memoria Fiscal',
  `daily_closes_counter` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Contador de cierres diarios Z',
  `registered_enterprise_fiscal_document_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'RIF',
  `printer_register_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Número de Registro de la Máquina',
  `printer_current_time` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Hora actual de la impresora (HHMMSS)',
  `printer_current_date` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Fecha actual de la impresora (DDMMAA)',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de actualizacion de este estado',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

