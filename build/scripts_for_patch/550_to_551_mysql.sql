-- MySQL upgrade script for Sugar 5.5.0 to 5.5.1

create index `idx_calls_par_del`  on `calls` (`parent_id`, `parent_type`, `deleted` );

create index `idx_mail_to`  on `email_cache` (`toaddr` );

alter table `outbound_email` modify column `type` varchar(15) NOT NULL default 'user',
							 modify column `mail_sendtype` varchar(8) NOT NULL default 'smtp';

--DROP TABLE `iframes`;

--DROP TABLE `feeds`;