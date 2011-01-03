<?php

    /**
     * glue
     *
     * Provides an easy way to map URLs to classes. URLs can be literal
     * strings or regular expressions.
     *
     * When the URLs are processed:
     *      * deliminators (/) are automatically escaped: (\/)
     *      * The beginning and end are anchored (^ $)
     *      * An optional end slash is added (/?)
     *	    * The i option is added for case-insensitive searches
     *
     * Example:
     *
     * $urls = array(
     *     '/' => 'index',
     *     '/page/(\d+) => 'page'
     * );
     *
     * class page {
     *      function GET($matches) {
     *          echo "Your requested page " . $matches[1];
     *      }
     * }
     *
     * $glue = new glue($urls);
     *
     */
    class glue {

        /**
         * stick
         *
         * the main static function of the glue class.
         *
         * @param   array    	$urls  	    The regex-based url to class mapping
         * @throws  Exception               Thrown if corresponding class is not found
         * @throws  Exception               Thrown if no match is found
         * @throws  BadMethodCallException  Thrown if a corresponding GET,POST is not found
         *
         */
        static function __construct( $urls ) {
            if( count( $urls ) == 0 ) {
                throw new Exception( 'No URLs given.' );
            }
            $method = strtoupper( $_SERVER['REQUEST_METHOD'] );
            $path = $_SERVER['REQUEST_URI'];
            krsort( $urls );
            foreach( $urls as $regex => $class ) {
                if( preg_match( '#^'.$regex.'/?#i', $path, $matches ) ) {
                    $this->loader();
                    if( class_exists( $class ) ) {
                        $obj = new $class;
                        if( method_exists( $obj, $method ) ) {
                            $obj->args = $matches;
                            $obj->$method();
                            return $obj;
                        }
                        else {
                            throw new BadMethodCallException( 'Method "'.$method.'" not supported.' );
                        }
                    }
                    else {
                        throw new Exception( 'Class "'.$class.'" not found.' );
                    }
                }
            }
            throw new Exception( 'URL "'.$path.'" not found.' );
        }

        static function loader( $class ) {
        	$file = $this->class.'.class.php';
			if( !is_readable( $file ) ) { return false; }
			require_once( $file );
			return true;
        }
    }
