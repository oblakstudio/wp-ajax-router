<?php //phpcs:disable SlevomatCodingStandard.Classes.SuperfluousExceptionNaming
namespace XWP\Exception;

class UnauthorizedException extends \Sunrise\Http\Router\Exception\Exception {
    /**
     * Gets a method
     *
     * @return string
     *
     * @since 2.9.0
     */
    public function getMethod(): string {
        return $this->fromContext( 'method', '' );
    }

    /**
     * Gets allowed methods
     *
     * @return string[]
     */
    public function getAllowedMethods(): array {
        return $this->fromContext( 'allowed', array() );
    }

    /**
     * Gets joined allowed methods
     *
     * @return string
     */
    public function getJoinedAllowedMethods(): string {
        return \implode( ',', $this->getAllowedMethods() );
    }
}
