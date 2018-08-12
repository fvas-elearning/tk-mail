<?php
namespace Tk\Mail;

/**
 * This message accepts text templates and replaces {param} with the
 * corresponding value.
 *
 *
 *
 */
class CurlyMessage extends Message
{
    
    use \Tk\CollectionTrait;

    /**
     * @var callable
     */
    protected $onParse = null;

    /**
     * @var \Tk\CurlyTemplate
     */
    protected $template = null;


    /**
     * MessageTemplate constructor.
     * 
     * @param string $body
     * @param string $subject
     * @param string $to
     * @param string $from
     */
    public function __construct($body = '{content}', $subject = '', $to = '', $from = '')
    {
        parent::__construct($body, $subject, $to, $from);
        $this->set('content', '');
    }

    /**
     * The message text body
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        $this->template = null;
        if ($body)
            $this->template = \Tk\CurlyTemplate::create($body);
        return $this;
    }

    /**
     * Set a callback function to fire when the getParsed() method is called
     *
     * @param callable $onParse
     * @return $this
     */
    public function setOnParse($onParse)
    {
        $this->onParse = $onParse;
        return $this;
    }

    /**
     * Gets the tCurlyTemplate object
     *
     * This will return null until the setBody($body) function is called with data
     *
     * @return \Tk\CurlyTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Returns the a parsed message body ready for sending.
     *
     * @return string
     * @throws \Tk\Exception
     */
    public function getParsed()
    {
        if (!$this->template) return '';

        $this->set('subject', $this->getSubject());
        $this->set('fromEmail', $this->getFrom());
        $this->set('toEmail', self::listToStr($this->getTo()));
        $this->set('toEmailList', self::listToStr($this->getTo()));
        $this->set('ccEmailList', self::listToStr($this->getCc()));
        $this->set('bccEmailList', self::listToStr($this->getBcc()));
        $this->set('date', \Tk\Date::create()->format(\Tk\Date::FORMAT_LONG_DATETIME));

        if (is_callable($this->onParse)) {
            call_user_func_array($this->onParse, array($this));
        }

        $str = $this->template->parse($this->getCollection()->all());

        return $str;
    }


    /**
     * @return array
     */
    public static function getParamList()
    {
        return array(
            'subject',
            'fromEmail',
            'toEmail',
            'toEmailList',
            'ccEmailList',
            'bccEmailList'
        );
    }

}