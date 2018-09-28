/*!
 * This file is a part of Mibew Broadcast Plugin
 *
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

$(document).ready(function(){
    $('#sidebar ul li:first-child ul.submenu li:nth-child(2)')
        .after('<li id="broadcast"><a href="javascript:void(0)">'
                + Mibew.Localization.trans('Broadcast to')
                + '</a><select id="broadcast_mode"><option value="all">'
                + Mibew.Localization.trans('all')
                + '</option><option value="chats">'
                + Mibew.Localization.trans('chats')
                + '</option><option value="queue">'
                + Mibew.Localization.trans('queue')
                + '</option><option value="operators">'
                + Mibew.Localization.trans('operators')
                + '</option></select></li>');
    $('#broadcast').click(function(e){
        Mibew.Utils.prompt(Mibew.Localization.trans('Enter the message:'), function(value) {
            if (value) {
                Mibew.Objects.server.callFunctions(
                    [{
                        'function': 'broadcastMessage',
                        'arguments': {
// required arguments
                            'agentId': Mibew.Objects.Models.agent.get('id'),
                            'return': {},
                            'references': {},
// arguments needed
                            'message': value,
                            'mode': $("#broadcast_mode").val()

                        }
                    }],
                    function(args) {},
                    true
                );
            }
        });
    });
});
