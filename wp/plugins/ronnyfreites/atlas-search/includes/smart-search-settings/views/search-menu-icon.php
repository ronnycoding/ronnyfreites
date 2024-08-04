<?php
/**
 * Custom Content Model Entry Icon
 *
 * @see https://stackoverflow.com/a/42265057
 * @package ContentEngine
 */

$atlas_search_entry_icon = <<<ICON
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M10 8C8.89543 8 8 8.89543 8 10C8 10.5523 7.55228 11 7 11C6.44772 11 6 10.5523 6 10C6 7.79086 7.79086 6 10 6C10.5523 6 11 6.44772 11 7C11 7.55228 10.5523 8 10 8Z" fill="white"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M2 10.5C2 5.80558 5.80558 2 10.5 2C15.1944 2 19 5.80558 19 10.5C19 12.4869 18.3183 14.3145 17.176 15.7618L21.7071 20.2929C22.0976 20.6834 22.0976 21.3166 21.7071 21.7071C21.3166 22.0976 20.6834 22.0976 20.2929 21.7071L15.7618 17.176C14.3145 18.3183 12.4869 19 10.5 19C5.80558 19 2 15.1944 2 10.5ZM10.5 4C6.91015 4 4 6.91015 4 10.5C4 14.0899 6.91015 17 10.5 17C14.0899 17 17 14.0899 17 10.5C17 6.91015 14.0899 4 10.5 4Z" fill="white"/>
</svg>
ICON;

return 'data:image/svg+xml;base64,' . base64_encode( $atlas_search_entry_icon ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
