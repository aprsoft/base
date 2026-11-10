Actualiza los nuevos campos de la tabla Details.

UPDATE details
SET
    indExe       = 1
    QtyItem      = cantidad,
    PrcExeItem   = valor,  
    MontoExeItem = cantidad*valor
    where cantidad > 0 and valor > 0