/**
  * Copyright (c) Enalean, 2013. All rights reserved
  *
  * This file is a part of Tuleap.
  *
  * Tuleap is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * Tuleap is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with Tuleap. If not, see <http://www.gnu.org/licenses/
  */

(function ($) {
    function openid_transition(event) {
        event.preventDefault();

        $('#login-openid').fadeOut(200);
        $(this).replaceWith($('#login-openid-form'));
        $('#login-openid-form').fadeIn(200);
        $('#login-openid-input').focus();
    }

    $(document).ready(function () {
        $('#login-openid').on('click', openid_transition);
    });
})(jQuery);