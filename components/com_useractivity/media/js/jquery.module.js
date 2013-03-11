/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

var modUA =
{
    /**
     * Ajax request object
     *
     * @var   object
     */
    rq: null,

    /**
     * Ajax request cache
     *
     * @var   array
     */
    rq_cache: [],


    /**
     * Function to update the activity stream in a module
     *
     * @param    string    fi        The form id name
     * @param    string    mid       The module id
     * @param    string    limit     The amount of records to fetch
     * @param    string    dir       The order direction of the query
     * @param    boolean   force     Ignore busy state if true
     *
     * @return   boolean          True on success, False on error
     */
    getItems: function(fi, mid, limit, dir, force)
    {
        var f = jQuery('#' + fi + mid);

        // Check if the form exists
        if (f.length == 0) return false;

        // Do nothing if busy
        if (jQuery('input[name|="busy"]', f).val() == '1' && force != true) {
            if (dir == 'filter' && modUA.rq != null) {
                modUA.rq.abort();
            }
            else {
                return false;
            }
        }

        var ls  = jQuery('input[name|="limitstart"]', f);
        var t   = jQuery('input[name|="total"]', f);
        var lsv = parseInt(ls.val());
        var tv  = parseInt(t.val());

        // Set to busy and adjust the query limit based on the direction
        if (jQuery('input[name|="busy"]', f).val() == '0') {
            modUA.setBusy(f, mid, '1');

            switch (dir)
            {
                case 'next':
                    lsv += limit;
                    if (lsv > tv) lsv = tv;
                    break;

                case 'prev':
                    lsv -= limit;
                    if (lsv < 0) lsv = 0;
                    break;

                case 'filter':
                    lsv = 0;
                    break;
            }

            ls.val(lsv);

            // Handle navigation buttons
            modUA.updateNav(lsv, tv, limit, mid);
        }

        // Serialize the form
        var d  = f.serializeArray();
        var ck = jQuery.param(d);

        var cached = false;

        // Check if the result is in the cache
        for (var cid in modUA.rq_cache)
        {
            if (cid == ck) {
                cached = true;
                break;
            }
        }

        // Request items
        var res = null;

        if (cached) {
            res = modUA.rq_cache[ck];
        }
        else {
            modUA.rqItems(f, d);

            // Process request
            return modUA.rq.done(function(r)
            {
                if (modUA.isJsonString(r) == false) {
                    // Something went wrong
                    modUA.err(r);
                    modUA.setBusy(f, mid, '0');
                    modUA.updateNav(lsv, tv, limit, mid);
                    modUA.rq_cache[ck] = false;

                    return false;
                }
                else {
                    res = jQuery.parseJSON(r);

                    // Add to cache
                    modUA.rq_cache[ck] = res;
                    return modUA.getItems(fi, mid, limit, dir, true);
                }
            });
        }

        if (res == null)  return true;
        if (res == false) return false;

        var t  = jQuery('#activities-' + mid);
        var tc = t.children();
        var c  = (tc.length + res['items'].length);
        var i  = 0;
        var l  = limit;

        if (dir == 'next' || dir == 'filter') {
            var e = tc.filter(':first');

            if (dir == 'filter') c = l * 2;
        }
        else {
            var e = tc.filter(':last');
        }

        // Hide old entries
        while(c > l)
        {
            if (i > 0) {
                if (dir == 'next' || dir == 'filter') {
                    e = e.next();
                }
                else {
                    e = e.prev();
                }
            }

            if (e.length) {
                e.delay(1).slideDown(100, function() { jQuery(this).remove(); });
            }

            i++; c--;
        }

        if (dir == 'prev') {
            res['items'] = res['items'].reverse();
        }

        // Show new ones
        for(i = 0; i < res['items'].length; i++)
        {
            if (dir == 'next' || dir == 'filter') {
                t.append(res['items'][i]);
                t.children().filter(':last').slideDown(100);
            }
            else {
                t.prepend(res['items'][i]);
                t.children().filter(':first').slideDown(100);
            }
        }

        // Update the total
        jQuery('input[name|="total"]', f).val(parseInt(res['total']));
        tv = parseInt(res['total']);

        modUA.setBusy(f, mid, '0');
        modUA.updateNav(lsv, tv, limit, mid);

        return true;
    },


    /**
     * Function to update the navigation of a module
     *
     * @param    integer    lsv     The list start offset value
     * @param    integer    tv      The total count of records in the db
     * @param    integer    limit   The list limit value
     * @param    string     mid     The module id
     *
     * @return   void
     */
    updateNav: function(lsv, tv, limit, mid)
    {
        var bn = jQuery('.actbtn-next-' + mid);
        var bp = jQuery('.actbtn-prev-' + mid);

        if (lsv == tv || (lsv + limit) > tv) {
            bn.addClass('disabled');

            if (tv > limit && tv > 0) {
                bp.removeClass('disabled');
            }
            else {
                bp.addClass('disabled');
            }
        }
        else {
            if (tv > limit) {
                bn.removeClass('disabled');
            }
            else {
                bn.addClass('disabled');
            }

            if (lsv >= limit) {
                bp.removeClass('disabled');
            }
            else {
                bp.addClass('disabled');
            }
        }
    },


    /**
     * Function to set the module state as "busy", meaning that a request is in progress
     *
     * @param    object    f      The module form object
     * @param    string    mid    The module id
     * @param    string    s      The module state to set. '1' = busy, '0' = idle
     *
     * @return   void
     */
    setBusy: function(f, mid, s)
    {
        jQuery('input[name|="busy"]', f).val(s);

        var bn = jQuery('.actbtn-next-' + mid);
        var bp = jQuery('.actbtn-prev-' + mid);

        if (s == '1') {
            bn.addClass('disabled');
            bp.addClass('disabled');
            f.addClass('disabled');
        }
        else {
            bn.removeClass('disabled');
            bp.removeClass('disabled');
            f.removeClass('disabled');
        }
    },


    /**
     * Function to make an ajax request to the user activity component.
     *
     * @param    object    f    The form object
     * @param    string    d    The serialized form data (Optional)
     *
     * @return   void
     */
    rqItems: function(f, d)
    {
        if (typeof d == 'undefined') {
            d = f.serializeArray();
        }

        modUA.rq = jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d) + '&tmpl=component&format=json',
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            error: function(resp, e, msg)
            {
                modUA.err(msg);
            }
        });
    },


    /**
     * Function to quickly check if a string is in Json format.
     * Not exactly precise, but good enough
     *
     * @param    string    str     The string to check
     *
     * @return   boolean           True if in Json format, False if not
     */
    isJsonString: function(str)
    {
        if (typeof str == 'undefined') return false;

        var l = str.length;
        var e = l - 1;

        if (l == 0) return false;

        if (str[0] != '{' && str[0] != '[') return false;
        if (str[e] != '}' && str[e] != ']') return false;

        return true;
    },


    /**
     * Function to append an error message to the joomla message container.
     * It it cannot find the container, will send an alert
     *
     * @param    string    msg     The message to show
     *
     * @return   void
     */
    err: function(msg)
    {
        var mc = jQuery('#system-message-container');

        if (typeof mc == 'undefined') {
            alert(msg);
        }
        else {
            if (msg != 'abort') mc.append(msg);
        }
    }
}