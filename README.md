目录

1. 用户

1.1 登录

1.2 注册

---

**1\.1\. 登录**

###### URL
> [/user/login](http://www.api.com/user/login)

###### 接口功能
> 根据用户和密码验证用户并登录

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|说明|
|:-----  |:-------|:-----|-----                               |
|name    |true    |string|用户名                          |
|password    |true    |string   |密码明文|

###### 返回字段（JSON）
> |返回字段|字段类型|说明                              |
|:-----   |:------|:-----------------------------   |
|code   |int    |返回结果状态。0：错误；1：正常。   |
|data  |string | 用户信息                      |
|error |array |错误信息                        |

###### 接口示例
> 地址：[http://www.api.com/user/login](http://jz.api.com/user/login)

**1\.2\. 注册**
###### URL
> [/user/register](http://www.api.com/user/register)

###### 接口功能
> 通过用户名，邮箱，密码注册

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|说明|
|:-----  |:-------|:-----|-----                               |
|name    |true    |string|用户名                              |
|email    |true    |string|邮箱，唯一，用于找回密码                          |
|password    |true    |string   |密码明文|
|password_confirmation    |true    |string   |确认密码，必须和password相同|

###### 返回字段（JSON）
> |返回字段|字段类型|说明                              |
|:-----   |:------|:-----------------------------   |
|code   |int    |返回结果状态。0：错误；1：正常。   |
|data  |string | 新注册用户信息                      |
|error |array |错误信息                        |

###### 接口示例
> 地址：[http://www.api.com/user/register](http://jz.api.com/user/register)
``` javascript
{
    "code": 0,
    "data": [],
    "error": {
        "name": [
            "用户名已存在，请输入其他名字"
        ],
        "email": [
            "邮箱地址已存在，请输入真实邮箱，之后用以找回密码"
        ]
    }
}
```
