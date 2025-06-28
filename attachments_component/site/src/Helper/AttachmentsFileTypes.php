<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Site\Helper;

use Joomla\String\StringHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * A utility class to help deal with file types
 *
 * @package Attachments
 */
class AttachmentsFileTypes
{
    /** Array of lookups for icon filename given a filename extension */
    static $attachments_icon_from_file_extension =
        array( 'aif' => 'music.gif',
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

    /**
     * Array of lookups for FontAwesome icon given a filename extension
     *
     * @access  public
     *
     * @var     array
     *
     * @since   1.0.0
     */
    public const ATTACHMENTS_ICON_FROM_FILE_EXTENSION_FA = [
        'aif' => 'fa-file-audio',
        'aiff' => 'fa-file-audio',
        'avi' => 'fa-file-video',
        'avif' => 'fa-file-image',
        'bmp' => 'fa-file-image',
        'bz2' => 'fa-file-archive',
        'c' => 'fa-file-code',
        'c++' => 'fa-file-code',
        'cab' => 'fa-file-archive',
        'cc' => 'fa-file-code',
        'cpp' => 'fa-file-code',
        'css' => 'fa-file-code',
        'csv' => 'fa-file-lines',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'eps' => 'fa-file-image',
        'gif' => 'fa-file-image',
        'gz' => 'fa-file-archive',
        'h' => 'fa-file-code',
        'iv' => 'fa-file-image',
        'jpg' => 'fa-file-image',
        'js' => 'fa-file-code',
        'midi' => 'fa-file-audio',
        'mov' => 'fa-file-video',
        'mp3' => 'fa-file-audio',
        'mpeg' => 'fa-file-video',
        'mpg' => 'fa-file-video',
        'odg' => 'fa-file-image',
        'odp' => 'fa-file-powerpoint',
        'ods' => 'fa-file-excel',
        'odt' => 'fa-file-word',
        'pdf' => 'fa-file-pdf',
        'php' => 'fa-file-code',
        'png' => 'fa-file-image',
        'pps' => 'fa-file-powerpoint',
        'ppt' => 'fa-file-powerpoint',
        'pptx' => 'fa-file-powerpoint',
        'ps' => 'fa-file-image',
        'ra' => 'fa-file-audio',
        'ram' => 'fa-file-audio',
        'rar' => 'fa-file-archive',
        'rtf' => 'fa-file-lines',
        'sql' => 'fa-file-code',
        'swf' => 'fa-file-video',
        'tar' => 'fa-file-archive',
        'txt' => 'fa-file-lines',
        'vcf' => 'fa-address-card',
        'vrml' => 'fa-file-image',
        'wav' => 'fa-file-audio',
        'webp' => 'fa-file-image',
        'wma' => 'fa-file-audio',
        'wmv' => 'fa-file-video',
        'wrl' => 'fa-file-image',
        'xls' => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'xml' => 'fa-file-code',
        'zip' => 'fa-file-archive',

        // Artificial
        '_generic' => 'fa-file',
        '_link' => 'fa-link',
    ];

    /** Array of lookups for icon filename from mime type */
    static $attachments_icon_from_mime_type =
        array( 'application/bzip2' => 'archive.gif',
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
               'application/x-zip-compressed' => 'zip.gif',
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

    /**
     * Array of lookups for FontAwesome icon from mime type
     *
     * @access  public
     *
     * @var     array
     *
     * @since   1.0.0
     */
    public const ATTACHMENTS_ICON_FROM_MIME_TYPE_FA = [
        'application/bzip2' => 'fa-file-archive',
        'application/excel' => 'fa-file-excel',
        'application/msword' => 'fa-file-word',
        'application/pdf' => 'fa-file-pdf',
        'application/postscript' => 'fa-file-image',
        'application/powerpoint' => 'fa-file-powerpoint',
        'application/rtf' => 'fa-file',
        'application/vnd.ms-cab-compressed' => 'fa-file-archive',
        'application/vnd.ms-excel' => 'fa-file-excel',
        'application/vnd.ms-powerpoint' => 'fa-file-powerpoint',
        'application/vnd.ms-pps' => 'fa-file-powerpoint',
        'application/vnd.ms-word' => 'fa-file-word',
        'application/vnd.oasis.opendocument.graphics' => 'fa-file-image',
        'application/vnd.oasis.opendocument.presentation' => 'fa-file-powerpoint',
        'application/vnd.oasis.opendocument.spreadsheet' => 'fa-file-excel',
        'application/vnd.oasis.opendocument.text' => 'fa-file-word',
        'application/vnd.openxmlformats' => 'fa-file-code',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
        'application/x-bz2' => 'fa-file-archive',
        'application/x-gzip' => 'fa-file-archive',
        'application/x-javascript' => 'fa-file-code',
        'application/x-midi' => 'fa-file-audio',
        'application/x-shockwave-flash' => 'fa-file-video',
        'application/x-rar-compressed' => 'fa-file-archive',
        'application/x-tar' => 'fa-file-archive',
        'application/x-vrml' => 'fa-file-image',
        'application/x-zip' => 'fa-file-archive',
        'application/x-zip-compressed' => 'fa-file-archive',
        'application/xml' => 'fa-file-code',
        'audio/mpeg' => 'fa-file-audio',
        'audio/x-aiff' => 'fa-file-audio',
        'audio/x-ms-wma' => 'fa-file-audio',
        'audio/x-pn-realaudio' => 'fa-file-audio',
        'audio/x-wav' => 'fa-file-audio',
        'image/avif' => 'fa-file-image',
        'image/bmp' => 'fa-file-image',
        'image/gif' => 'fa-file-image',
        'image/jpeg' => 'fa-file-image',
        'image/png' => 'fa-file-image',
        'image/webp' => 'fa-file-image',
        'model/vrml' => 'fa-file-image  ',
        'text/css' => 'fa-file-code',
        'text/html' => 'fa-file',
        'text/plain' => 'fa-file-lines',
        'text/rtf' => 'fa-file-lines',
        'text/x-vcard' => 'fa-address-card',
        'video/mpeg' => 'fa-file-video',
        'video/quicktime' => 'fa-file-video',
        'video/x-ms-wmv' => 'fa-file-video',
        'video/x-msvideo' => 'fa-file-video',

        // Artificial
        'link/generic' => 'fa-file',
        'link/unknown' => 'fa-link'
    ];

    /** Array of lookups for mime type from filename extension */
    static $attachments_mime_type_from_extension =
        array( 'aif' => 'audio/x-aiff',
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

    /** Array of known PDF mime types */
    static $attachments_pdf_mime_types =
        array('application/pdf',
              'application/x-pdf',
              'application/vnd.fdf',
              'application/download',
              'application/x-download',
              'binary/octet-stream'
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
        if (($mime_type == 'link/unknown') or ($mime_type == 'unknown')) {
            return 'link.gif';
        }
        if ($mime_type == 'link/broken') {
            return 'link_bad.gif';
        }

        if ($filename) {
            // Make sure it is a real filename
            if (strpos($filename, '.') === false) {
                // Do not know any better, assume it is text
                return 'text/plain';
            }

            $path_info = pathinfo($filename);

            // Try the extension first
            $extension = StringHelper::strtolower($path_info['extension']);
            if (array_key_exists($extension, AttachmentsFileTypes::$attachments_icon_from_file_extension)) {
                $iconf = AttachmentsFileTypes::$attachments_icon_from_file_extension[$extension];
                if (StringHelper::strlen($iconf) > 0) {
                    return $iconf;
                }
            }
        } else {
            // Try the mime type
            if (array_key_exists($mime_type, AttachmentsFileTypes::$attachments_icon_from_mime_type)) {
                $iconf = AttachmentsFileTypes::$attachments_icon_from_mime_type[$mime_type];
                if ($iconf && (StringHelper::strlen($iconf) > 0)) {
                    return $iconf;
                }
            }
        }

        return '';
    }

    /**
     * Get the FontAwesome icon name for a specific filename (or mime type)
     *
     * @access  public
     *
     * @param   string  $filename  the filename to check
     * @param   string  $mimeType  the MIME type to check (if the filename fails)
     *
     * @return  string  the FontAwesome icon name (or '' if none is found)
     *
     * @since   4.1.2
     */
    public static function fa_icon_filename(string $filename, string $mimeType): string
    {
        // Recognize some special cases first
        if (($mimeType == 'link/unknown') || ($mimeType == 'unknown')) {
            return 'fa-link';
        }

        if ($mimeType == 'link/broken') {
            return 'fa-link-slashed';
        }

        if ($filename) {
            // Make sure it is a real filename
            if (strpos($filename, '.') === false) {
                // Do not know any better, assume it is text
                return 'fa-file-lines';
            }

            $pathInfo = pathinfo($filename);

            // Try the extension first
            $extension = StringHelper::strtolower($pathInfo['extension']);

            if (array_key_exists($extension, self::ATTACHMENTS_ICON_FROM_FILE_EXTENSION_FA)) {
                return self::ATTACHMENTS_ICON_FROM_FILE_EXTENSION_FA[$extension];
            }
        } else {
            // Try the mime type
            if (array_key_exists($mimeType, self::ATTACHMENTS_ICON_FROM_MIME_TYPE_FA)) {
                return self::ATTACHMENTS_ICON_FROM_MIME_TYPE_FA[$mimeType];
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

        // Make sure it is a real filename
        if (strpos($filename, '.') === false) {
            return 'unknown';
        }

        // Try the extension first
        $extension = strtolower($path_info['extension']);
        if (array_key_exists($extension, AttachmentsFileTypes::$attachments_mime_type_from_extension)) {
            $mime_type = AttachmentsFileTypes::$attachments_mime_type_from_extension[$extension];
            if (strlen($mime_type) > 0) {
                return $mime_type;
            }
        }

        return false;
    }
}
