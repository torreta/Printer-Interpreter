cambiar las tablas de document_id a default 1 para prevenir el error de array null de php al mover entre tablas


-------------------
Verificar Flags con comando D 
    en el bodegon, no he podido verificarlas personalmente por no tener a la mano un reporte D

Cocos:

    21 - 30
    63 - 02 < --- Verificar


Hacer archivo de configuracion

---------------------

crear archivos configuracion parl padding fiscales
    BIXOLON 350
    SRP-350
    HKA-80
    HKA80_VE (con flags)

----------------------


select
-- count(*) as n,
dbo_administration_invoices.id,
dbo_administration_invoices.invoice_number,
dbo_administration_invoices.saleorder_number,
dbo_administration_invoices.total as invoice_total,
dbo_administration_invoices.real_total as invoice_real_total,
dbo_finance_payments.amount as payment_amount,
dbo_config_exchange_rates.currency_id as exchange_id_currency_id,
dbo_config_exchange_rates.exchange_rate as exchange_rate ,
dbo_finance_payment_types.name as payment_type,
dbo_config_currencies.name as payment_currency,
dbo_config_currencies.abbreviation as currency_sign,
dbo_finance_payments.* 
from dbo_finance_payments
join dbo_administration_invoices on dbo_administration_invoices.id = dbo_finance_payments.invoice_id 
join dbo_config_exchange_rates on dbo_administration_invoices.exchange_rate_id = dbo_config_exchange_rates.id 
join dbo_finance_payment_types on dbo_finance_payment_types.id = dbo_finance_payments.payment_type_id 
join dbo_config_currencies on dbo_config_currencies.id = dbo_finance_payments.currency_id 
-- group by invoice_number 
-- having n > 2
where dbo_administration_invoices.id = 1210


auxquery