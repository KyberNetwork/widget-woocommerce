# TX Monitoring

In this plugin, in order to monitor transaction for an order, we use [wiget-monitor-php](https://github.com/KyberNetwork/widget-monitor-php). That plugin will retrieve transaction receipt from blockchain and return status (also validate the payment). It provides 2 modes for monitoring tx status, `useIntervalLoop` or not. If using `useIntervalLoop`, the plugin will continueing query to a node to get tx receipt until it get reach the block confirmation number. This approach will consume a lot of server resources if there are many orders on-hold and big block confirmation number. 

So, it is recommended to use a cronjob to check order tx status periodically. We are already using [wp_cron](https://codex.wordpress.org/Function_Reference/wp_cron) to run monitor function every 30 seconds. You can install and use [WP Crontrol](https://vi.wordpress.org/plugins/wp-crontrol/) to view and edit that job.


![https://i.imgur.com/KICHkkT.png](https://i.imgur.com/KICHkkT.png)


## Advance options
Those cron jobs above will run every page load, it has its own 2 main problems: first, with any heavy job it will slowdown the page for users, second, it will depend on user request for runing the cronjob. Therefore, it is recommended for disable WordPress default cronjob and setup Linux (server) cronjob using crontab. Here is the instruction:

1. Install crontab (if your server does not have it as default): here is one instruct for [ubuntu](https://www.rosehosting.com/blog/ubuntu-crontab/), you might want to search for instruction for your own kind of server (Centos, RHEL, etc)

2. Setup crontab:

- **Edit crontab**


```shell
crontab -e
```

- Then edit file as below

```shell
* * * * * curl http://example.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

or

```shell
* * * * * curl http://example.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

as we want them to run every minute (for checking tx status)

*Please make sure you use correct path to wp-cron.php.*

Alternatively, you can use WP-Cli

```shell
* * * * * cd /var/www/example.com/htdocs; wp cron event run --due-now > /dev/null 2>&1
```

3. Now you might want to disable Wordpress default wp-cron:

Add following to `wp-config.php`:

```php
define('DISABLE_WP_CRON', true);
```

4. Finally, restart crontab service so new job can run:

```shell
sudo service cron restart
```