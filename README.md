![image-20200305113514031](https://raw.githubusercontent.com/JerryChan93/pics/master/laravel-commoned-use/image-20200305113514031.png)



目前对项目进行了如下分层与职责划分：

**Validator**：做基础的数据合法性验证

**Controller**：验证数据合法性（调用Validator)，接收 HTTP request 与 调用 Service

**Service**：辅助 Controller 处理业务逻辑（可调用 Repository）

**Repository**：辅助 model 用来处理数据层

**Model** ：配置文件



![image-20200305115253919](https://raw.githubusercontent.com/JerryChan93/pics/master/laravel-commoned-use/image-20200305115253919.png)

上图的箭头表示模块之间 **依赖注入** 的方向。



说说好处：

单一职责：使代码更为简洁易懂，提高了可维护性；

依赖反转：降低了模块之间的耦合度；

总的来说：代码能更容易的进行 unit test 和 feature test，

​					通过提高代码的 可读性、可维护性等，

​					来降低了日后 开发 与 维护成本。



谈谈使用：

## Controller：

![image-20200305130346702](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305130346702.png?raw=true)

​					

左边为采用现有架构。

先看 右边开头的数据校验的逻辑，

全部被封装到了 _validator 里面，

而具体的业务逻辑 则在 _service 里面处理，

整个 function 相当简洁明了。



## Validator

![image-20200305130953862](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305130953862.png?raw=true)

Validator 是借鉴了 TP5 Validation 里面的 场景值思想 

与 laravel Validator 做的一层封装。

场景值 能更好的提高了 代码的复用 与 简化调用流程。

举两个栗子

新增的验证逻辑调用：

`$validator->scene('create')->check($data);`

修改状态的验证逻辑调用：

`$validator->scene('update_status')->check($data);`



当 框架内置验证函数都不能满足的时候，

我们可以对应的拓展自定义 验证方法（详情先看 BaseValidator）。



总结：validator 可以极大的 简化我们做数据校验的复杂度，

与 减少coding很多重复的逻辑判断。



## Service：



![image-20200305132136471](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305132136471.png?raw=true)

![image-20200305132447581](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305132447581.png?raw=true)

一般做web 开发的常规的业务上的需求都是 增删改查，

在 BaseService 做了 __call 函数的处理，

以简化 简单的业务代码无需重复构建，可直接 调用 Repository

（Repository 层分装了 基础的增删改查 功能）。



举个两个栗子：

实例化调用：

`$servier->create($data);`

单例调用：

`MenuService::getInstance()->create($data);`



## Repository:



![image-20200305133415150](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305133415150.png)



Repository 旨在简化 Model 层的逻辑（满足 Model层 做个配置项），

有关 数据层的 处理，全都应该交由 Repository  层。



好处：

不会再出现 整个项目 到处都充斥着 model，

尤其在项目 臃肿的情况前提下，

想查找某块逻辑，

可能得需要 到处翻找方可找到（很低效）。

还有就是后期 如果需要替换 数据库（如 MongoDB 或者 Redis），

可以直接 通过 修改对应的Repository 就可以了，

而且不会 影响上层（service 与 controller）的逻辑。

![image-20200305134150848](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305134150848.png?raw=true)



大多数常用方法都是采用 都采用了 组件的形式（Trait）来进行封装，

这样子能比 继承 拥有更高的灵活度。





## WhereQueryBuilder ：

whereQueryBuilder 是基于 Laravel Builder 做的抽象层，

这是用来 简化 Where 语句的。



举个栗子：

![image-20200305134913690](https://github.com/JerryChan93/pics/blob/master/laravel-commoned-use/image-20200305134913690.png?raw=true)



上面的代码充斥了大量的 where 条件语句，

```php
$whereList = [
  'where' => [
    	'lso.ship_order_status',
    	'lso.countries',
    	...
  ],
  'whereBetween' => [
    	'eso.record_at' => ['start_at', 'end_at'],
  ],
];
$query = $model->query();
$whereBuilder = new WhereQueryBuilder($whereList);
$bQuery = $whereBuilder->build($parms, $query);
```

（上面的代码只是大概的描述，仅供参考，

更多的实现方式请细看 WhereQueryBuilder）

可以发现，在采用 WhereQueryBuilder 之后，
可以以 近似 配置的形式来生成想要的SQL 语句，

能极大 的简化 查询语句的复杂度。

