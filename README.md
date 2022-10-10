# 监控文件变化并自动重启进程

## 使用说明

```
bash-5.1# php watch.php  --help
Usage: watch.php [ARG...]

Command Options:
  -i, --include  包含路径，多个路径用“,”分隔
  -e, --exclude  排除的路径，多个路径用“,”分隔
  -t, --interval 监视文件间隔时间（秒）
  -c, --cmd      程序启动命令
  -s, --signal   发送给子进程的信号。默认值为9，即SIGKILL

Global Options:
  -h, --help     Print usage
  -v, --version  Print version information

Run 'watch.php --help' for more information on a command.

Developed with Mix PHP framework. (openmix.org/mix-php)
```

## 使用示例

```bash
php watch.php \
  --include="./" \
  -exclude="/.idea/,/vendor/,/runtime/,*.log" \
  --cmd="php ./bin/swoole.php"
```

## License

[Apache-2.0](http://www.apache.org/licenses/LICENSE-2.0.html)
