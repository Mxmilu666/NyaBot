# NyaBot

_✨ 一个简单,极易上手的基于PHP以及 [Swoole](https://www.swoole.com/) 的 [QQ官方机器人](https://q.qq.com/) 简单WebSocket/HttpSDK ✨_  

## 🎈已经实现接口

<details>
<summary>已实现群聊接口</summary>

- [x] 发送群聊被动消息

- More TODO...(咕咕咕)

</details>

## ⚙️快速部署
1.下载[swoole-cli](https://www.swoole.com/download)对应的系统版本

2.将此仓库Clone/Download下来
``` code
git clone https://github.com/Mxmilu666/NyaBot.git
```
3.在/inc/config.php中配置Apidomain,AppID,Token和AppSecret

4.执行
``` code
./swoole-cli bot.php
```
~~5.点一个stars~~

## 📁文件目录
``` code
.
├── LICENSE
├── README.md
├── bot.php //主程序
├── class
│   └── example.class.php //全局函数示例
├── inc
│   ├── config.php //配置文件
│   ├── core.class.php //核心参数库
│   └── mlog.class.php //Mlog库
└── plugins
    └── example.php //简单的示例(等我有空了补一个详细的(因为懒))

```

## 📍Todo
- [ ] 支持频道信息(缓慢)
- [ ] 重连时使用[恢复登录态 Session](https://bot.q.qq.com/wiki/develop/api-v2/dev-prepare/interface-framework/event-emit.html#%E6%81%A2%E5%A4%8D%E7%99%BB%E5%BD%95%E6%80%81-session)接口
- [x] 兼容[NyaBot-Gocq](https://github.com/Mxmilu666/NyaBot-Gocq)插件
- [x] 可以使用

## 📖许可证
项目采用`Apache-2.0 license`协议开源

## 🫂感谢
[Swoole](https://www.swoole.com/)提供的高性能PHP协程框架

[1626424216](https://github.com/1626424216)提供的大部分框架代码和思路

### 这个项目只是开源我正在使用的框架,不一定可以满足所有人的需求,但是欢迎大家Pr(逃
