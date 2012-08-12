<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/**
 * A utility class to help deal with file types
 *
 * @package Attachments
 */
class AttachmentsFileTypes {

	/** Array of lookups for icon filename given a filename extension */
	static $attachments_icon_from_file_extension =
		Array( 'aif' => 'music.gif',
			   'aiff' => 'music.gif',
			   'avi' => 'video.gif',
			   'bmp' => 'image.gif',
			   'bz2' => 'archive.gif',
			   'c' => 'c.gif',
			   'c++' => 'cpp.gif',
			   'cab' => 'zip.gif',
			   'cc' => 'cpp.gif',
			   'cpp' => 'cpp.gif',
			   'css' => 'css.gif',
			   'csv' => 'csv.gif',
			   'doc' => 'word.gif',
			   'docx' => 'wordx.gif',
			   'eps' => 'eps.gif',
			   'gif' => 'image.gif',
			   'gz' => 'archive.gif',
			   'h' => 'h.gif',
			   'iv' => '3d.gif',
			   'jpg' => 'image.gif',
			   'js' => 'js.gif',
			   'midi' => 'midi.gif',
			   'mov' => 'mov.gif',
			   'mp3' => 'music.gif',
			   'mpeg' => 'video.gif',
			   'mpg' => 'video.gif',
			   'odg' => 'oo-draw.gif',
			   'odp' => 'oo-impress.gif',
			   'ods' => 'oo-calc.gif',
			   'odt' => 'oo-write.gif',
			   'pdf' => 'pdf.gif',
			   'php' => 'php.gif',
			   'png' => 'image.gif',
			   'pps' => 'ppt.gif',
			   'ppt' => 'ppt.gif',
			   'pptx' => 'pptx.gif',
			   'ps' => 'ps.gif',
			   'ra' => 'audio.gif',
			   'ram' => 'audio.gif',
			   'rar' => 'archive.gif',
			   'rtf' => 'rtf.gif',
			   'sql' => 'sql.gif',
			   'swf' => 'flash.gif',
			   'tar' => 'archive.gif',
			   'txt' => 'text.gif',
			   'vcf' => 'vcard.gif',
			   'vrml' => '3d.gif',
			   'wav' =>  'audio.gif',
			   'wma' => 'music.gif',
			   'wmv' => 'video.gif',
			   'wrl' => '3d.gif',
			   'xls' => 'excel.gif',
			   'xlsx' => 'excelx.gif',
			   'xml' => 'xml.gif',
			   'zip' => 'zip.gif',
		
			   // Artificial
			   '_generic' => 'generic.gif',
			   '_link' => 'link.gif',
			   );

	/** Array of lookups for icon filename from mime type */
	static $attachments_icon_from_mime_type =
		Array( 'application/bzip2' => 'archive.gif',
			   'application/excel' => 'excel.gif',
			   'application/msword' => 'word.gif',
			   'application/pdf' => 'pdf.gif',
			   'application/postscript' => 'ps.gif',
			   'application/powerpoint' => 'ppt.gif',
			   'application/vnd.ms-cab-compressed' => 'zip.gif',
			   'application/vnd.ms-excel' => 'excel.gif',
			   'application/vnd.ms-powerpoint' => 'ppt.gif',
			   'application/vnd.ms-pps' => 'ppt.gif',
			   'application/vnd.ms-word' => 'word.gif',
			   'application/vnd.oasis.opendocument.graphics' => 'oo-draw.gif',
			   'application/vnd.oasis.opendocument.presentation' => 'oo-impress.gif',
			   'application/vnd.oasis.opendocument.spreadsheet' => 'oo-calc.gif',
			   'application/vnd.oasis.opendocument.text' => 'oo-write.gif',
			   'application/vnd.openxmlformats' => 'xml.gif',
			   'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx.gif', 
			   'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'ppt.gif',
			   'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx.gif',
			   'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'wordx.gif',
			   'application/x-bz2' => 'archive.gif',
			   'application/x-gzip' => 'archive.gif',
			   'application/x-javascript' => 'js.gif', 
			   'application/x-midi' => 'midi.gif', 
			   'application/x-shockwave-flash' => 'flash.gif',
			   'application/x-rar-compressed' => 'archive.gif',
			   'application/x-tar' => 'archive.gif', 
			   'application/x-vrml' => '3d.gif', 
			   'application/x-zip' => 'zip.gif', 
			   'application/xml' => 'xml.gif',
			   'audio/mpeg' => 'music.gif',
			   'audio/x-aiff' => 'music.gif',
			   'audio/x-ms-wma' => 'music.gif',
			   'audio/x-pn-realaudio' => 'audio.gif', 
			   'audio/x-wav' => 'audio.gif', 
			   'image/bmp' => 'image.gif',
			   'image/gif' => 'image.gif',
			   'image/jpeg' => 'image.gif',
			   'image/png' => 'image.gif',
			   'model/vrml' => '3d.gif',
			   'text/css' => 'css.gif',
			   'text/html' => 'generic.gif',
			   'text/plain' => 'text.gif',
			   'text/rtf' => 'rtf.gif',
			   'text/x-vcard' => 'vcard.gif',
			   'video/mpeg' => 'video.gif',
			   'video/quicktime' => 'mov.gif',
			   'video/x-ms-wmv' => 'video.gif',
			   'video/x-msvideo' => 'video.gif',

			   // Artificial
			   'link/generic' => 'generic.gif',
			   'link/unknown' => 'link.gif'
			   );

	/** Array of lookups for mime type from filename extension */
	static $attachments_mime_type_from_extension =
		Array( 'aif' => 'audio/x-aiff',
			   'aiff' => 'audio/x-aiff',
			   'avi' => 'video/x-msvideo',
			   'bmp' => 'image/bmp',
			   'bz2' => 'application/x-bz2',
			   'c' => 'text/plain',
			   'c++' => 'text/plain',
			   'cab' => 'application/vnd.ms-cab-compressed',
			   'cc' => 'text/plain',
			   'cpp' => 'text/plain',
			   'css' => 'text/css',
			   'csv' => 'text/csv',
			   'doc' => 'application/msword',
			   'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			   'eps' => 'application/postscript',
			   'gif' => 'image/gif',
			   'gz' => 'application/x-gzip',
			   'h' => 'text/plain',
			   'iv' => 'graphics/x-inventor',
			   'jpg' => 'image/jpeg',
			   'js' => 'application/x-javascript',
			   'midi' => 'application/x-midi',
			   'mov' => 'video/quicktime',
			   'mp3' => 'audio/mpeg',
			   'mpeg' => 'audio/mpeg',
			   'mpg' => 'audio/mpeg',
			   'odg' => 'application/vnd.oasis.opendocument.graphics',
			   'odp' => 'application/vnd.oasis.opendocument.presentation',
			   'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			   'odt' => 'application/vnd.oasis.opendocument.text',
			   'pdf' => 'application/pdf',
			   'php' => 'text/plain',
			   'png' => 'image/png',
			   'pps' => 'application/vnd.ms-powerpoint',
			   'ppt' => 'application/vnd.ms-powerpoint',
			   'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			   'ps' => 'application/postscript',
			   'ra' => 'audio/x-pn-realaudio',
			   'ram' => 'audio/x-pn-realaudio',
			   'rar' => 'application/x-rar-compressed',
			   'rtf' => 'application/rtf',
			   'sql' => 'text/plain',
			   'swf' => 'application/x-shockwave-flash',
			   'tar' => 'application/x-tar',
			   'txt' => 'text/plain',
			   'vcf' => 'text/x-vcard',
			   'vrml' => 'application/x-vrml',
			   'wav' => 'audio/x-wav',
			   'wma' => 'audio/x-ms-wma',
			   'wmv' => 'video/x-ms-wmv',
			   'wrl' => 'x-world/x-vrml',
			   'xls' => 'application/vnd.ms-excel',
			   'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			   'xml' => 'application/xml',
			   'zip' => 'application/x-zip'
			   );


	/**
	 * Get the icon filename for a specific filename (or mime type)
	 *
	 * @param string $filename the filename to check
	 * @param string $mime_type the MIME type to check (if the filename fails)
	 *
	 * @return the icon filename (or '' if none is found)
	 */
	public static function icon_filename($filename, $mime_type)
	{
		// Recognize some special cases first
		if ( $mime_type == 'link/unknown' ) {
			return 'link.gif';
			}
		if ( $mime_type == 'link/broken' ) {
			return 'link_bad.gif';
			}

		if ( $filename ) {

			$path_info = pathinfo($filename);

			// Try the extension first
			$extension = JString::strtolower($path_info['extension']);
			if ( array_key_exists( $extension, AttachmentsFileTypes::$attachments_icon_from_file_extension ) ) {
				$iconf = AttachmentsFileTypes::$attachments_icon_from_file_extension[$extension];
				if ( JString::strlen($iconf) > 0 )	{
					return $iconf;
					}
				}
			}

		else {

			// Try the mime type
			if ( array_key_exists( $mime_type, AttachmentsFileTypes::$attachments_icon_from_mime_type ) ) {
				$iconf = AttachmentsFileTypes::$attachments_icon_from_mime_type[$mime_type];
				if ( $iconf && (JString::strlen($iconf) > 0) ) {
					return $iconf;
					}
				}
			}

		return '';
	}

	
	/**
	 * Get an array of unique icon filenames
	 *
	 * @return an array of unique icon filenames
	 */
	public static function unique_icon_filenames()
	{
		$vals = array_unique(array_values(AttachmentsFileTypes::$attachments_icon_from_file_extension));
		sort($vals);
				
		return $vals;
	}


	/**
	 * Get the mime type for a specific file
	 *
	 * @param string $filename the filename to check
	 *
	 * @return the mime type string
	 */
	public static function mime_type($filename)
	{
		$path_info = pathinfo($filename);
				
		// Try the extension first
		$extension = strtolower($path_info['extension']);
		if ( array_key_exists($extension, AttachmentsFileTypes::$attachments_mime_type_from_extension) ) {
			$mime_type = AttachmentsFileTypes::$attachments_mime_type_from_extension[$extension];
			if ( strlen($mime_type) > 0 ) 
				return $mime_type;
			}
				
		return false;
	}

}
