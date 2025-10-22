Regla SQLite hosting:
- usar siempre cadenas 'Y-m-d H:i:s' al guardar/consultar fechas
- evitar funciones avanzadas (strftime, whereDate, json_extract, etc.)
- no usar lockForUpdate ni features modernas; preferir DB::connection('tenant')->table(...)

