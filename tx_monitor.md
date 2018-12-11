# TX Monitoring

In this plugin, in order to monitor transaction for an order, we use [wiget-monitor-php](https://github.com/KyberNetwork/widget-monitor-php). That plugin will retrieve transaction receipt from blockchain and return status (also validate the payment). It provides 2 modes for monitoring tx status, `useIntervalLoop` or not. If using `useIntervalLoop`, the plugin will continueing query to a node to get tx receipt until it get reach the block confirmation number. This approach will consume a lot of server resources if there are many orders on-hold and big block confirmation number. 

So, it is recommended to use a cronjob to check order tx status periodically. We are already using [wp_cron](https://codex.wordpress.org/Function_Reference/wp_cron) to run monitor function every 30 seconds. You can install and use [WP Crontrol](https://vi.wordpress.org/plugins/wp-crontrol/) to view and edit that job.


![https://i.imgur.com/KICHkkT.png](https://i.imgur.com/KICHkkT.png)