<?php get_header(); ?>
<div class="main">
    <h1>Site info</h1>
    <button type="button" name="ssl_btn" value="check_ssl_expire">Check SSL</button>
    <div class="ssl-result"></div>
    <h1>Australian</h1>
    <div class="content_wrapper">
        <form name="siteinfo" method="post">
            <select name="site_name" form="siteinfo">
                <option value="onlineauscasino.com">Onlineauscasino</option>
                <option value="gedex.ca">Gedex</option>
            </select>
            <input type="radio" id="req1" name="req" value="wp-version">
            <label for="vehicle1"> WordPress Version</label><br>
            <input type="radio" id="req2" name="req" value="plugins">
            <label for="vehicle2"> Plugins needs update</label><br>
            <input type="submit" value="Send Request">
            <input type="hidden" name="site" value="onlineauscasino.com">
        </form>
    </div>
    <div class="response_wrapper"></div>
</div>
<?php get_footer(); ?>

