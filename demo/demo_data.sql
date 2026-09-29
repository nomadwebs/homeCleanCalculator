-- Datos de EJEMPLO (ficticios) para probar la aplicación.
-- Tarifa de ejemplo: 12 €/h x 4 h por visita = 48 € por día.
--
-- Cómo usarlo: abre la aplicación una vez (así se crea la base de datos) e importa este
-- fichero en la base "home_clean_calculator" (phpMyAdmin > Importar). Hazlo con la base vacía;
-- si ya tienes datos, usa antes Configuración > "Borrar todos los datos".
-- No modifica tu configuración (tarifa, horas, comunidad).

USE home_clean_calculator;

INSERT INTO orders (year,month,status,total,closed_at) VALUES (2026,1,'closed',384.00,'2026-01-28 20:00:00');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-01-16',200,'Sin cambio');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-02',4,12,'2026-01-16 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-09',4,12,'2026-01-16 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-13',4,12,'2026-01-16 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-16',4,12,'2026-01-16 12:00:00',@p);
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-02-03',190,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-20',4,12,'2026-02-03 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-23',4,12,'2026-02-03 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-27',4,12,'2026-02-03 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-01-30',4,12,'2026-02-03 12:00:00',@p);

INSERT INTO orders (year,month,status,total,closed_at) VALUES (2026,2,'closed',384.00,'2026-02-28 20:00:00');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-03-06',380,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-03',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-06',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-10',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-13',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-17',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-20',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-24',4,12,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-02-27',4,12,'2026-03-06 12:00:00',@p);

INSERT INTO orders (year,month,status) VALUES (2026,3,'open');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-03-20',190,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-03-03',4,12,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-03-06',4,12,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-03-10',4,12,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,paid_at,payment_id) VALUES (@o,'2026-03-13',4,12,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate) VALUES (@o,'2026-03-17',4,12);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate) VALUES (@o,'2026-03-20',4,12);
