-- Oracle upgrade script for Sugar 5.5.0 to 5.5.1

create index idx_calls_par_del  on calls (parent_id, parent_type, deleted );

create index idx_mail_to  on email_cache (toaddr );

ALTER TABLE OUTBOUND_EMAIL MODIFY(TYPE VARCHAR2(15),
        						  MAIL_SENDTYPE  DEFAULT 'smtp');
--DROP TABLE `iframes`;

--DROP TABLE `feeds`;