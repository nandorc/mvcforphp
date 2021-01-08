--
-- Tabla de registros de envío de correo
--
drop table if exists maillogs;
create table maillogs(
    cod varchar(10) not null comment 'Código único de autenticación',
    crtdt datetime not null comment 'Fecha de creación del registro',
    expdt datetime not null comment 'Fecha máxima de expiración de la autenticación',
    request varchar(100) comment 'Archivo de mailing solicitado',
    primary key(cod) comment 'Asignación del cod como PK'
) comment 'Tabla de registros de email';