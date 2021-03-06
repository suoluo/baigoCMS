<?php
return "<h3>分页参数</h3>
    <p>
        在所有需要用到分页的地方，都有该参数，如：栏目、TAG、专题、搜索等。参数的数组名一般为 <code>{\$tplData.pageRow}</code>。在模板中需要根据参数来进行分页，详情请查看系统默认模板 <mark>./bg_tpl/pub/default/include/page.tpl</mark>。
    </p>
    <div class=\"panel panel-default\">
        <div class=\"table-responsive\">
            <table class=\"table table-bordered\">
                <thead>
                    <tr>
                        <th class=\"text-nowrap\">键名</th>
                        <th>说明</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class=\"text-nowrap\">page</td>
                        <td>当前页码</td>
                        <td> </td>
                    </tr>
                    <tr>
                        <td class=\"text-nowrap\">p</td>
                        <td>分组数</td>
                        <td>页数过多时，需要将分页按钮分成若干组，系统默认是 10 页一组。</td>
                    </tr>
                    <tr>
                        <td class=\"text-nowrap\">begin</td>
                        <td>分组起始页码</td>
                        <td>每一个分组的开始页码。</td>
                    </tr>
                    <tr>
                        <td class=\"text-nowrap\">end</td>
                        <td>分组结束页码</td>
                        <td>每一个分组的结束页码。</td>
                    </tr>
                    <tr>
                        <td class=\"text-nowrap\">total</td>
                        <td>总页数</td>
                        <td> </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p>&nbsp;</p>

    <h4>栏目分页数据示例</h4>
    <code>{\$tplData.pageRow|@print_r}</code>
    <p>
<pre><code class=\"language-php\">Array (
    [page] => 1 //当前页号
    [p] => 0 //分组数
    [begin] => 1 //分组起始页码
    [end] => 1 //分组结束页码
    [total] => 1 //总页数
)</code></pre>
    </p>

    <p>&nbsp;</p>

    <h4>分页处理 Smarty 示例代码</h4>
    <p>
<pre><code class=\"language-smarty\">{\$str_url = &quot;{\$tplData.cateRow.urlRow.cate_url}{\$tplData.cateRow.urlRow.page_attach}&quot;}
&lt;ul&gt;
    {if \$tplData.pageRow.page &gt; 1} {* 如果当前页码大于 1，则显示首页链接 *}
        &lt;li&gt;
            &lt;a href=&quot;{\$str_url}1&quot;&gt;首页&lt;/a&gt;
        &lt;/li&gt;
    {/if}

    {if \$tplData.pageRow.p * 10 &gt; 0} {* 如果当前分组大于分组数，则显示上一组链接 *}
        &lt;li&gt;
            &lt;a href=&quot;{\$str_url}{\$tplData.pageRow.p * 10}&quot;&gt;上十页&lt;/a&gt;
        &lt;/li&gt;
    {/if}

    &lt;li&gt;
        {if \$tplData.pageRow.page &lt;= 1} {* 如果当前页码小于 1，则上一页按钮为空白，否则加上链接 *}
            &lt;span&gt;&laquo;&lt;/span&gt;
        {else}
            &lt;a href=&quot;{\$str_url}{\$tplData.pageRow.page - 1}&quot;&gt;&laquo;&lt;/a&gt;
        {/if}
    &lt;/li&gt;

    {for \$_iii = \$tplData.pageRow.begin to \$tplData.pageRow.end} {* 分组循环，从分组起始页码至分组结束页码 *}
        &lt;li&gt;
            {if \$_iii == \$tplData.pageRow.page} {* 如果循环中的页码等于当前页，则为空白，否则加上链接 *}
                &lt;span&gt;{\$_iii}&lt;/span&gt;
            {else}
                &lt;a href=&quot;{\$str_url}{\$_iii}&quot;&gt;{\$_iii}&lt;/a&gt;
            {/if}
        &lt;/li&gt;
    {/for}

    &lt;li&gt;
        {if \$tplData.pageRow.page &gt;= \$tplData.pageRow.total} {* 如果当前页码大于总页数，则下一页按钮为空白，否则加上链接 *}
            &lt;span&gt;&raquo;&lt;/span&gt;
        {else}
            &lt;a href=&quot;{\$str_url}{\$tplData.pageRow.page + 1}&quot;&gt;&raquo;&lt;/a&gt;
        {/if}
    &lt;/li&gt;

    {if \$tplData.pageRow.end &lt; \$tplData.pageRow.total} {* 如果分组结束页码小于总页数，则显示下一组链接 *}
        &lt;li&gt;
            &lt;a href=&quot;{\$str_url}{\$_iii}&quot;&gt;下十页&lt;/a&gt;
        &lt;/li&gt;
    {/if}

    {if \$tplData.pageRow.page &lt; \$tplData.pageRow.total} {* 如果当前页码小于总页数，则显示末页链接 *}
        &lt;li&gt;
            &lt;a href=&quot;{\$str_url}{\$tplData.pageRow.total}&quot;&gt;末页&lt;/a&gt;
        &lt;/li&gt;
    {/if}
&lt;/ul&gt;</code></pre>
    </p>";
