Add symfony command

In config.yaml add :
```
monolog:
    channels: ['m01tom02']
    handlers:
        main:
            type:  stream
            path:  %kernel.logs_dir%/%kernel.environment%.log
            level: debug
        backup2nas:
            type: stream
            path:  %kernel.logs_dir%/m01tom02.log
            channels: [m01tom02]
```
With : 
```
set /etc/logrotate.d

/XXXXXXX/*.log {
    su XXXXXXX XXXXXXX
    rotate 3
    size 1M
    missingok
    notifempty
    create 644 XXXXXXX XXXXXXX
}
```


