-- Datos de EJEMPLO (ficticios) para probar la aplicación.
-- Persona 1 "Limpiadora": 12 €/h x 4 h por visita = 48 € por día.
-- Persona 2 "Canguro": 9 €/h x 2 h por visita = 18 € por día, con algún gasto extra (merienda).
--
-- Cómo usarlo: abre la aplicación una vez (así se crea la base de datos) e importa este
-- fichero en la base "home_clean_calculator" (phpMyAdmin > Importar). Hazlo con la base vacía;
-- si ya tienes datos, usa antes Configuración > "Borrar todos los datos".
-- Ojo: ajusta la tarifa de la persona 1 y crea la persona 2 (si no existía).
-- Para probar sin riesgo, lo más cómodo es el interruptor de Configuración > Base de datos > "Cambiar a la base de PRUEBAS".

USE home_clean_calculator;

UPDATE workers SET name = 'Limpiadora', hourly_rate = 12, default_hours = 4, color = '#0f766e' WHERE id = 1;
INSERT IGNORE INTO workers (id, name, hourly_rate, default_hours, color) VALUES (2, 'Canguro', 9, 2, '#b45309');

INSERT INTO orders (worker_id,year,month,status,total,closed_at) VALUES (1,2026,1,'closed',384.00,'2026-01-28 20:00:00');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-01-16',200,'Sin cambio');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-02',4,12,0,NULL,'2026-01-16 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-09',4,12,0,NULL,'2026-01-16 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-13',4,12,0,NULL,'2026-01-16 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-16',4,12,0,NULL,'2026-01-16 12:00:00',@p);
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-02-03',190,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-20',4,12,0,NULL,'2026-02-03 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-23',4,12,0,NULL,'2026-02-03 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-27',4,12,0,NULL,'2026-02-03 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-01-30',4,12,0,NULL,'2026-02-03 12:00:00',@p);

INSERT INTO orders (worker_id,year,month,status,total,closed_at) VALUES (1,2026,2,'closed',384.00,'2026-02-28 20:00:00');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-03-06',380,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-03',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-06',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-10',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-13',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-17',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-20',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-24',4,12,0,NULL,'2026-03-06 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-27',4,12,0,NULL,'2026-03-06 12:00:00',@p);

INSERT INTO orders (worker_id,year,month,status) VALUES (1,2026,3,'open');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-03-20',190,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-03',4,12,0,NULL,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-06',4,12,0,NULL,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-10',4,12,0,NULL,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-13',4,12,0,NULL,'2026-03-20 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note) VALUES (@o,'2026-03-17',4,12,0,NULL);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note) VALUES (@o,'2026-03-20',4,12,0,NULL);

INSERT INTO orders (worker_id,year,month,status,total,closed_at) VALUES (2,2026,2,'closed',153.50,'2026-02-28 20:00:00');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-03-02',150,'Redondeo');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-03',2,9,0,NULL,'2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-05',2,9,0,NULL,'2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-10',2,9,0,NULL,'2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-12',2,9,3.5,'Merienda y fruta','2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-17',2,9,0,NULL,'2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-19',2,9,0,NULL,'2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-24',2,9,6,'Material manualidades','2026-03-02 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-02-26',2,9,0,NULL,'2026-03-02 12:00:00',@p);

INSERT INTO orders (worker_id,year,month,status) VALUES (2,2026,3,'open');
SET @o = LAST_INSERT_ID();
INSERT INTO payments (order_id,paid_on,amount,note) VALUES (@o,'2026-03-13',60,'Sin cambio');
SET @p = LAST_INSERT_ID();
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-03',2,9,0,NULL,'2026-03-13 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-05',2,9,4,'Merienda','2026-03-13 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note,paid_at,payment_id) VALUES (@o,'2026-03-10',2,9,0,NULL,'2026-03-13 12:00:00',@p);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note) VALUES (@o,'2026-03-12',2,9,0,NULL);
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note) VALUES (@o,'2026-03-17',2,9,2.5,'Autobús');
INSERT INTO order_lines (order_id,work_date,hours,hourly_rate,extra_amount,extra_note) VALUES (@o,'2026-03-19',2,9,0,NULL);
