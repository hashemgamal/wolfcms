<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS.
 *
 * Wolf CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Wolf CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wolf CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Wolf CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The multi lang plugin redirects users to a page with content in their language.
 *
 * The redirect only occurs when a user's indicated preferred language is
 * available. There are multiple methods to determine the desired language.
 * These are:
 *
 * - HTTP_ACCEPT_LANG header
 * - URI based language hint (for example: http://www.example.com/en/page.html
 * - Preferred language setting of logged in users
 *
 * @package wolf
 * @subpackage plugin.multi_lang
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.0.0
 * @since Wolf version 0.7.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2010
 */

Plugin::setInfos(array(
    'id'          => 'multi_lang',
    'title'       => __('Multiple Languages'),
    'description' => __('Provides language specific content when available based on user preferences.'),
    'version'     => '1.0.0',
   	'license'     => 'GPL',
	'author'      => 'Martijn van der Kleijn',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml',
    'require_wolf_version' => '0.6.0'
));

Plugin::addController('multi_lang', __('Multiple Languages'), 'administrator', false);

// Observe the necessary events.
$style = Plugin::getSetting('style', 'multi_lang');
if (false !== $style && $style == 'tab') {
    Observer::observe('page_found', 'replaceContent');
}

/**
 * Replaces the content of the 'body' part if a language specific part exists.
 *
 * @param Page $page Page object.
 */
function replaceContent($page) {
    $source = Plugin::getSetting('langsource', 'multi_lang');
    if (!$source) return;

    if ($source == 'header') {
        use_helper('I18n');
        $found = false;

        foreach (I18n::getPreferredLanguages() as $lang) {
            if ( Setting::get('language') == $lang) { break; }

            if ( isset($page->part->$lang) && !empty($page->part->$lang->content_html) && $page->part->$lang->content_html != '' ) {
                $page->part->body->content_html = $page->part->$lang->content_html;
                $found = true;
            }

            if ($found) break;
        }
    }
    else if ($source == 'url') {
        // not implemented yet
    }
    else if ($source == 'preferences') {
        AuthUser::load();
        if (AuthUser::isLoggedIn()) {
            $lang = AuthUser::getRecord()->language;

            if ( isset($page->part->$lang) && !empty($page->part->$lang->content_html) && $page->part->$lang->content_html != '' ) {
                $page->part->body->content_html = $page->part->$lang->content_html;
            }
        }
    }
}