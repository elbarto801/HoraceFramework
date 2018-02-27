<?php
namespace HoraceFramework;

class Core
{
    const   PACKAGE                 =   'HoraceFramework';
    const   SUBPACKAGE              =   'Core';
    const   AUTHOR                  =   'Emmanuel NOEL';
    const   EDITOR                  =   'Horace Productions';
    const   VERSION                 =   '1.0';
    const   HTTP_CODE               =   array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended');

    const   HTTP_MSG                =   array(
        400 => "Your browser sent a request that this server could not understand.",
        401 => "This server could not verify that you are authorized to access the document requested.",
        402 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
        403 => "You don't have permission to access %s on this server.",
        404 => "We couldn't find that uri on our server, though it's most certainly not your fault.",
        405 => "The requested method is not allowed for the URL %s.",
        406 => "An appropriate representation of the requested resource %s could not be found on this server.",
        407 => "An appropriate representation of the requested resource %s could not be found on this server.",
        408 => "Server timeout waiting for the HTTP request from the client.",
        409 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
        410 => "The requested resource %s is no longer available on this server and there is no forwarding address. Please remove all references to this resource.",
        411 => "A request of the requested method GET requires a valid Content-length.",
        412 => "The precondition on the request for the URL %s evaluated to false.",
        413 => "The requested resource %s does not allow request data with GET requests, or the amount of data provided in the request exceeds the capacity limit.",
        414 => "The requested URL's length exceeds the capacity limit for this server.",
        415 => "The supplied request data is not in a format acceptable for processing by this resource.",
        416 => 'Requested Range Not Satisfiable',
        417 => "The expectation given in the Expect request-header field could not be met by this server. The client sent <code>Expect:</code>",
        422 => "The server understands the media type of the request entity, but was unable to process the contained instructions.",
        423 => "The requested resource is currently locked. The lock must be released or proper identification given before the method can be applied.",
        424 => "The method could not be performed on the resource because the requested action depended on another action and that other action failed.",
        425 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
        426 => "The requested resource can only be retrieved using SSL. Either upgrade your client, or try requesting the page using https://",
        500 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
        501 => "This type of request method to %s is not supported.",
        502 => "The proxy server received an invalid response from an upstream server.",
        503 => "The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.",
        504 => "The proxy server did not receive a timely response from the upstream server.",
        505 => 'The server encountered an internal error or misconfiguration and was unable to complete your request.',
        506 => "A variant for the requested resource <code>%s</code> is itself a negotiable resource. This indicates a configuration error.",
        507 => "The method could not be performed.  There is insufficient free space left in your storage allocation.",
        510 => "A mandatory extension policy in the request is not accepted by the server for this resource."
    );

    const   SERVER_PROTOCOL         =   array('HTTP/0.9', 'HTTP/1.0', 'HTTP/1.1');

    private $_fw_root           =   null,
            $_autoload_object   =   null,
            $_environnement     =   'developpement',
            $_server_protocol   =   false,
            $_exceptions_loaded =   false;

    public function __construct()
    {
        // On vérifie si la version minimale de PHP est acquise.
        if (PHP_VERSION < self::MINIMAL_PHP_VERSION)
        {
            echo sprintf('The minimum version of PHP must be %s', self::MINIMAL_PHP_VERSION);exit;
        }

        // On récupère le type d'environnement.
        $this->_environnement = isset($_SERVER['HP_ENV']) ? $_SERVER['HP_ENV'] : $this->_environnement;

        // On récupère le type protocole serveur.
        if (!in_array($_SERVER['SERVER_PROTOCOL'], self::SERVER_PROTOCOL))
        {
            header('HTTP/1.1 505 HTTP Version Not Supported', true, 505);
            echo self::HTTP_MSG[505];
            exit;
        } else
        {
            $this->_server_protocol = $_SERVER['SERVER_PROTOCOL'];
        }

        // On definit le root de l'application.
        if ($this->_fw_root === null)
        {
            $this->_fw_root = dirname($_SERVER['DOCUMENT_ROOT']);
        }

        // On remplace le caractère \ par /
        $this->_fw_root = str_replace('\\', '/', $this->_fw_root);

        // On charge la classe d'exceptions et d'erreurs.
        if (file_exists($this->_fw_root.'/lib/exceptions.class.php'))
        {
            include($this->_fw_root.'/lib/exceptions.class.php');
            $this->_exceptions_loaded = true;
        } else
        {
            $this->_exceptions_loaded = false;
        }

        // On défini les erreurs suivant l'environnement.
        switch($this->_environnement)
        {
            case 'production':
                if ($this->_exceptions_loaded)
                {
                    \HoraceFramework\Exceptions::errorReporting('E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED');
                }
                else
                {
                    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
                }
                ini_set('display_errors', 0);
                break;

            case 'developpement':
                if ($this->_exceptions_loaded)
                {
                    \HoraceFramework\Exceptions::errorReporting(-1);
                }
                else
                {
                    error_reporting(-1);
                }
                ini_set('display_errors', 1);
                break;

            default:
                header($this->_server_protocol.' 503 Service Unavailable', true, 503);
                echo self::HTTP_MSG[503];
                exit;
        }

        // On declare la fonction autoload
        if (file_exists($this->_app_root.'/core/autoload.class.php'))
        {
            include $this->_app_root . '/core/autoload.class.php';
            spl_autoload_register(array(__NAMESPACE__.'\Autoload','loader'));
            $this->_autoload_ready = true;
        }
    }

    /**
     * @return null|string
     */
    public static function getRoot()
    {
        return $this->_app_root;
    }

    public function getAutolaodStatus()
    {
        return $this->_autoload_ready;
    }
}
?>

