<?php
//============================================================+
// File name   : tcpdf_config.php
// Begin       : 2004-06-11
// Last Update : 2014-12-11
//
// Description : Configuration file for TCPDF.
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2004-2014  Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with TCPDF.  If not, see <http://www.gnu.org/licenses/>.
//
// See LICENSE.TXT file for more information.
//============================================================+

/**
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @package com.tecnick.tcpdf
 * @version 4.9.005
 * @since 2004-10-27
 */

// IMPORTANT:
// If you define the constant K_TCPDF_EXTERNAL_CONFIG, all the following settings will be ignored.
// If you use the tcpdf_autoconfig.php, then you can overwrite some values here.


$fdir = realpath( WOO_VOU_DIR.'/includes/tcpdf/fonts' );
if (substr($fdir, -1) != '/') {
	$fdir .= '/';
}
define ('WPWEB_K_PATH_FONTS', apply_filters( 'woo_vou_tcpdf_fonts_path', $fdir ) );



/**
 * Generic name for a blank image.
 */
define ('WPWEB_K_BLANK_IMAGE', '_blank.png');

/**
 * Page format.
 */
define ('WPWEB_PDF_PAGE_FORMAT', 'A4');

/**
 * Page orientation (P=portrait, L=landscape).
 */
define ('WPWEB_PDF_PAGE_ORIENTATION', 'P');

/**
 * Document creator.
 */
define ('WPWEB_PDF_CREATOR', 'TCPDF');

/**
 * Document author.
 */
define ('WPWEB_PDF_AUTHOR', 'TCPDF');

/**
 * Header title.
 */
define ('WPWEB_PDF_HEADER_TITLE', 'TCPDF Example');

/**
 * Header description string.
 */
define ('WPWEB_PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");

/**
 * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
 */
define ('WPWEB_PDF_UNIT', 'mm');

/**
 * Header margin.
 */
define ('WPWEB_PDF_MARGIN_HEADER', 5);

/**
 * Footer margin.
 */
define ('WPWEB_PDF_MARGIN_FOOTER', 10);

/**
 * Top margin.
 */
define ('WPWEB_PDF_MARGIN_TOP', 27);

/**
 * Bottom margin.
 */
define ('WPWEB_PDF_MARGIN_BOTTOM', 25);

/**
 * Left margin.
 */
define ('WPWEB_PDF_MARGIN_LEFT', 15);

/**
 * Right margin.
 */
define ('WPWEB_PDF_MARGIN_RIGHT', 15);

/**
 * Default main font name.
 */
//define ('PDF_FONT_NAME_MAIN', 'helvetica');
$defaultFont = 'helvetica';
define ('WPWEB_PDF_FONT_NAME_MAIN', apply_filters( 'woo_vou_tcpdf_main_font', $defaultFont ));

/**
 * Default main font size.
 */
define ('WPWEB_PDF_FONT_SIZE_MAIN', 10);

/**
 * Default data font name.
 */
//define ('PDF_FONT_NAME_DATA', 'helvetica');
define ('WPWEB_PDF_FONT_NAME_DATA', apply_filters( 'woo_vou_tcpdf_data_font', $defaultFont ));


/**
 * Default data font size.
 */
define ('WPWEB_PDF_FONT_SIZE_DATA', 8);

/**
 * Default monospaced font name.
 */
define ('WPWEB_PDF_FONT_MONOSPACED', 'courier');

/**
 * Ratio used to adjust the conversion of pixels to user units.
 */
define ('WPWEB_PDF_IMAGE_SCALE_RATIO', 1.25);

/**
 * Magnification factor for titles.
 */
define('WPWEB_HEAD_MAGNIFICATION', 1.1);

/**
 * Height of cell respect font height.
 */
define('WPWEB_K_CELL_HEIGHT_RATIO', 1.25);

/**
 * Title magnification respect main font size.
 */
define('WPWEB_K_TITLE_MAGNIFICATION', 1.3);

/**
 * Reduction factor for small font.
 */
define('WPWEB_K_SMALL_RATIO', 2/3);

/**
 * Set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language.
 */
define('WPWEB_K_THAI_TOPCHARS', true);

/**
 * If true allows to call TCPDF methods using HTML syntax
 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
 */
define('WPWEB_K_TCPDF_CALLS_IN_HTML', true);

/**
 * If true and PHP version is greater than 5, then the Error() method throw new exception instead of terminating the execution.
 */
define('WPWEB_K_TCPDF_THROW_EXCEPTION_ERROR', false);

/**
 * Default timezone for datetime functions
 */
define('WPWEB_K_TIMEZONE', 'UTC');

//============================================================+
// END OF FILE
//============================================================+
