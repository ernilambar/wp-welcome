<?php
/**
 * Utils class
 *
 * @package WPWelcome
 */

namespace Nilambar\Welcome;

/**
 * Utils class.
 *
 * @since 1.0.0
 */
class Utils {

	/**
	 * Render attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Attributes.
	 * @param bool  $echo Whether to echo or not.
	 */
	public static function render_attr( $attributes, $echo = true ) {
		if ( empty( $attributes ) ) {
			return;
		}

		$html = '';

		foreach ( $attributes as $name => $value ) {
			$esc_value = '';

			if ( 'class' === $name && is_array( $value ) ) {
				$value = join( ' ', array_unique( $value ) );
			}

			if ( false !== $value && 'href' === $name ) {
				$esc_value = esc_url( $value );

			} elseif ( false !== $value ) {
				$esc_value = esc_attr( $value );
			}

			if ( ! in_array( $name, array( 'class', 'id', 'title', 'style', 'name' ), true ) ) {
				$html .= false !== $value ? sprintf( ' %s="%s"', esc_html( $name ), $esc_value ) : esc_html( " {$name}" );
			} else {
				$html .= $value ? sprintf( ' %s="%s"', esc_html( $name ), $esc_value ) : '';
			}
		}

		if ( ! empty( $html ) && true === $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $html;
		}
	}
}
