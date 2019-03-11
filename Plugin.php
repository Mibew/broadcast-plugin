<?php
/*
 * Copyright 2018 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Mibew\Mibew\Plugin\Broadcast;

use Mibew\Database;
use Mibew\EventDispatcher\EventDispatcher;
use Mibew\EventDispatcher\Events;
use Mibew\Thread;

/**
 * Provides an ability to automatically reply a visitor in a queue.
 */
class Plugin extends \Mibew\Plugin\AbstractPlugin implements \Mibew\Plugin\PluginInterface
{
    protected $initialized = true;

    /**
     * Class constructor.
     *
     * @param array $config List of the plugin config.
     *
     */
    public function __construct($config)
    {
    }

    /**
     * Defines necessary event listeners.
     */
    public function run()
    {
        $dispatcher = EventDispatcher::getInstance();
        $dispatcher->attachListener(Events::USERS_FUNCTION_CALL, $this, 'usersFunctionCallHandler');
        $dispatcher->attachListener(Events::PAGE_ADD_JS, $this, 'addJs');
        $dispatcher->attachListener(Events::PAGE_ADD_CSS, $this, 'addCss');
    }

    /**
     * A handler for {@link \Mibew\EventDispatcher\Events::USERS_FUNCTION_CALL}.
     *
     * Provides an ability to use "openStreetMapGetInfo" function at the client
     * side.
     *
     * @see \Mibew\EventDispatcher\Events::USERS_FUNCTION_CALL
     */
    public function usersFunctionCallHandler(&$function)
    {
        if ($this->checkAdmin() && ($function['function'] == 'broadcastMessage')) {

            // Get message and its mode
            $message = $function['arguments']['message'];
            $mode = $function['arguments']['mode'];

            // Set default message type
            $message_type = Thread::KIND_INFO;

            // Get threads list depending on message mode
            $threads = array();
            if ($mode === 'chats') {
                // Message mode: all active chats
                $threads = Database::getInstance()->query(
                    'SELECT threadid FROM {thread} WHERE istate = :istate',
                    array(':istate' => Thread::STATE_CHATTING),
                    array('return_rows' => Database::RETURN_ALL_ROWS)
                );
            }
            elseif ($mode === 'operators') {
                // Message mode: operators only (all active chats and invitations)
                $threads = Database::getInstance()->query(
                    'SELECT threadid FROM {thread} WHERE istate = :istate1 or istate = :istate2',
                    array(':istate1' => Thread::STATE_CHATTING, ':istate2' => Thread::STATE_INVITED),
                    array('return_rows' => Database::RETURN_ALL_ROWS)
                );

                // Hide message from visitors
                $message_type = Thread::KIND_FOR_AGENT;
            }
            elseif ($mode === 'queue') {
                // Message mode: all opened chats without operators
                $threads = Database::getInstance()->query(
                    'SELECT threadid FROM {thread} WHERE istate = :istate1 or istate = :istate2',
                    array(':istate1' => Thread::STATE_QUEUE, ':istate2' => Thread::STATE_WAITING),
                    array('return_rows' => Database::RETURN_ALL_ROWS)
                );
            }
            elseif ($mode === 'all') {
                // Message mode: all chats (except for closed ones)
                $threads = Database::getInstance()->query(
                    'SELECT threadid FROM {thread} WHERE istate <> :istate',
                    array(':istate' => Thread::STATE_CLOSED),
                    array('return_rows' => Database::RETURN_ALL_ROWS)
                );
            }

            // Acually send messages
            foreach ($threads as $thread) {
                $thread_object = Thread::load($thread['threadid']);
                $thread_object->postMessage($message_type, $message);
            }

            $function['results'] = array();
        }
    }

    /**
     * Adds custom JS file to the page.
     *
     * @see \Mibew\EventDispatcher\Events::PAGE_ADD_JS
     */
    public function addJs(&$args)
    {
        if ($this->checkAdmin()) {
            if (!strcmp('/operator/users', $args['request']->getPathInfo())) {
                $args['js'][] = str_replace(DIRECTORY_SEPARATOR, '/', $this->getFilesPath()) . '/js/broadcast.js';
            }
        }
    }

    /**
     * Adds custom CSS file to the page.
     *
     * @see \Mibew\EventDispatcher\Events::PAGE_ADD_CSS
     */
    public function addCss(&$args)
    {
        if ($this->checkAdmin()) {
            if (!strcmp('/operator/users', $args['request']->getPathInfo())) {
                $args['css'][] = $this->getFilesPath() . '/css/broadcast.css';
            }
        }
    }

    /**
     * Checks whether an actual operator is an administrator.
     */
    protected function checkAdmin()
    {
        return (array_key_exists(SESSION_PREFIX . 'operator', $_SESSION)
                && is_capable(CAN_ADMINISTRATE, operator_by_id($_SESSION[SESSION_PREFIX . 'operator']['operatorid'])));
    }

    /**
     * Returns verision of the plugin.
     *
     * @return string Plugin's version.
     */
    public static function getVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public static function install()
    {
        // Initialize localization constants
        $constants = array(
            'Broadcast to',
            'all',
            'chats',
            'queue',
            'operators',
            'Enter the message:'
        );

        foreach ($constants as $constant) {
            getlocal($constant);
        }

        return true;
    }
}

