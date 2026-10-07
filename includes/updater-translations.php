<?php
/** Copy into the host adapter and replace the oxygen-quicknav textdomain with its literal domain. */
defined( 'ABSPATH' ) || exit;
return static function ( string $message ): string {
    // Literal calls let the host's normal translation extractor collect every source string.
    switch ( $message ) {
        case 'Private mode must be boolean.':
            return __( 'Private mode must be boolean.', 'oxygen-quicknav' );
        case 'Invalid authentication provider.':
            return __( 'Invalid authentication provider.', 'oxygen-quicknav' );
        case 'The plugin must be installed in a stable slug directory.':
            return __( 'The plugin must be installed in a stable slug directory.', 'oxygen-quicknav' );
        case 'Invalid GitHub repository URL.':
            return __( 'Invalid GitHub repository URL.', 'oxygen-quicknav' );
        case 'The private update could not be authorized. Check the repository credentials and refresh updates.':
            return __( 'The private update could not be authorized. Check the repository credentials and refresh updates.', 'oxygen-quicknav' );
        case 'Could not create the update download file.':
            return __( 'Could not create the update download file.', 'oxygen-quicknav' );
        case 'The private update download failed. Check credentials and try again.':
            return __( 'The private update download failed. Check credentials and try again.', 'oxygen-quicknav' );
        case 'Could not access the update filesystem.':
            return __( 'Could not access the update filesystem.', 'oxygen-quicknav' );
        case 'GitHub release does not contain the plugin main file.':
            return __( 'GitHub release does not contain the plugin main file.', 'oxygen-quicknav' );
        case 'Could not prepare the GitHub release package.':
            return __( 'Could not prepare the GitHub release package.', 'oxygen-quicknav' );
        case 'See the release on GitHub.':
            return __( 'See the release on GitHub.', 'oxygen-quicknav' );
        default:
            return $message;
    }
};
