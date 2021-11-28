<?php

namespace MuriloPerosa\TelegramReports;

use Exception;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

class Telegram {


    /**
     * Report Levels
     */
    const REPORT_LEVEL_INFO    = 'INFORMATION';
    const REPORT_LEVEL_WARNING = 'WARNING';
    const REPORT_LEVEL_DEBUG   = 'DEBUG';
    const REPORT_LEVEL_SUCCESS = 'SUCCESS';
    const REPORT_LEVEL_ERROR   = 'ERROR';
    const REPORT_LEVEL_CRITICAL_ERROR = 'CRITICAL_ERROR';

    /**
     * Telegram bot token.
     *
     * @var string
     */
    private $token;
    
    /**
     * Telegram chat id to messages.
     *
     * @var string
     */
    private $chat_id;
    
    /**
     * Instance of the BotApi.
     *
     * @var TelegramBot\Api\BotApi
     */
    private $bot;

    public function __construct(string $token, string $chat_id)
    {
        $this->token   = $token;
        $this->chat_id = $chat_id;
        $this->bot     = new BotApi($this->token);
    }

    /**
     * Send a text message with parse mode HTML.
     *
     * @param string $content
     * 
     * @return \TelegramBot\Api\Types\Message
     * @throws Exception
     */
    public function sendHtmlMessage (string $content) : Message
    {   
        return $this->bot->sendMessage($this->chat_id, $content, 'html');
    }

    /**
     * Send a pattern report.
     *
     * @param string $level
     * @param string $content
     * @param string|null $title
     * @return \TelegramBot\Api\Types\Message
     */
    public function report (string $level, string $content, string $title = null) : Message
    {
        $message = "<strong>{$this->getReportSignal($level)} {$this->getReportTitle($level, $title)}</strong> \n\n";
        $message.= $content;

        return $this->sendHtmlMessage($message);
    }

    /**
     * Reports an application exception
     *
     * @param Exception $e
     * @param boolean $is_critical
     * @return Message
     */
    public function reportException (Exception $e, bool $is_critical = false): Message
    {
        $level = $is_critical ? self::REPORT_LEVEL_CRITICAL_ERROR : self::REPORT_LEVEL_ERROR;
        $content  = "<strong>Error:</strong> {$e->getMessage()} \n";
        $content .= "<strong>Code:</strong> {$e->getCode()} \n";
        $content .= "<strong>File:</strong> {$e->getFile()}:{$e->getLine()} \n\n";
        $content .= "<strong>Stack Trace:</strong> \n\n{$e->getTraceAsString()}";
        return $this->report($level, $content);
    }

    /**
     * Returns report's signal for the specified level
     *
     * @param string $level
     * @return string
     */
    private function getReportSignal (string $level) : string
    {
        $signals = [
            self::REPORT_LEVEL_INFO    => "\xE2\x84\xB9",
            self::REPORT_LEVEL_WARNING => "\xE2\x9A\xA0",
            self::REPORT_LEVEL_DEBUG   => "\xF0\x9F\x91\xBE",
            self::REPORT_LEVEL_SUCCESS => "\xE2\x9C\x85",
            self::REPORT_LEVEL_ERROR   => "\xF0\x9F\x9A\xA9",
            self::REPORT_LEVEL_CRITICAL_ERROR  => "\xF0\x9F\x94\xA5",
        ];

        return isset($signals[$level]) ? $signals[$level] : $signals[self::REPORT_LEVEL_INFO];
    }

    /**
     * Returns the report's title considering level and custom title.
     *
     * @param string $level
     * @param string|null $title
     * @return string
     */
    private function getReportTitle (string $level, string $title = null) : string
    {
        return !empty($title) ? $title : ucwords(strtolower(str_replace('_', ' ', $level))) . "!";
    }
}