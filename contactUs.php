<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package Base
 * @subpackage Static
 */

require_once('header.php');

header('refresh: 4; url=modforum.php');

libHTML::notice('Redirecting to Mod forum', 'Redirecting you to the moderator forum where you can submit a request to the mod team.');

die();

libHTML::starthtml();

print libHTML::pageTitle(l_t('Contact the Moderators'),l_t('Learn how to contact the moderators and what information to include.'));


require_once(l_r('locales/English/contactUs.php'));

print '</div>';
libHTML::footer();
