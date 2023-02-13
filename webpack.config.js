const path = require( 'path' );

const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

module.exports = {
	...defaultConfig,
	entry: {
		'wp-welcome': path.resolve( __dirname, 'resources', 'wp-welcome.js' ),
	},
	output: {
		path: path.resolve( __dirname, 'assets' ),
		filename: '[name].js',
	},
};
