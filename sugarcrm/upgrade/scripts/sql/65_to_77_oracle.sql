/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

ALTER TABLE config add platform varchar2(32) NULL;
ALTER TABLE meetings ADD (duration_minutes_chr varchar2(4));
UPDATE meetings SET duration_minutes_chr = to_char(duration_minutes);
UPDATE meetings SET duration_minutes = NULL;
ALTER TABLE meetings MODIFY duration_minutes varchar2(4);
UPDATE meetings SET duration_minutes = duration_minutes_chr;
ALTER TABLE meetings DROP COLUMN duration_minutes_chr;
