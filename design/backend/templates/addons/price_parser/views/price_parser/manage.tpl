{** price_parser section **}

{capture name="mainbox"}

<style>
	.btnLine {
		margin-top: 15px;
	}
	.btnLine .text {

	}
</style>

<script>
	function confirmFunc(text) {
		if (confirm(text)) {
			return true;
		}

		return false;
	}
</script>

	<table>
		<tbody>
			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=downloadPrices"
						onclick="return confirmFunc('Вы действительно хотите обновить прайс листы?')" 
						class="btn btn-default">Обновить прайс листы</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Загрузка архивов с новыми прайс листами и их распаковка
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=clearDb"
						onclick="return confirmFunc('Вы действительно хотите очистить БД?')" 
						class="btn btn-default">Очистить БД</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Удаление из БД всех категорий, товаров, характеристик и изображений товаров
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=fillDb"
						onclick="return confirmFunc('Заполнить БД?')" 
						class="btn btn-default">Заполнить БД</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Заполнение категорий, товаров, скачивание изображений к товарам и добавление характеристик к товарам
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=updateCategories"
						onclick="return confirmFunc('Обновить категории?')" 
						class="btn btn-default">Обновить категории</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Удалить все категории кроме тех, которые внесены в анстройки аддона и добавить категории из прайс листа
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=updateProducts"
						onclick="return confirmFunc('Обновить товары?')" 
						class="btn btn-default">Обновить товары</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Удаление товаров, коотрые отсутствуют в прайс листе и добавление товаров, которых еще нет на сайте
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=updateProperties"
						onclick="return confirmFunc('Вы действительно хотите обновить прайс листы?')" 
						class="btn btn-default">Обновить характеристики</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Удаление всех характеристик товаров и добавление характеристик из прайс листа
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="/admin.php?dispatch=price_parser.manage&method=updatePrices"
						onclick="return confirmFunc('Обновить цены всех товаров?')" 
						class="btn btn-default">Обновить цены</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Обновление цен всех товаров, с учетом настроек наценки на категории
				</td>
			</tr>

            <tr style="border-bottom: 1px solid #D0D0D0;">
                <td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
                    <a href="/admin.php?dispatch=price_parser.manage&method=updateAmounts"
                       onclick="return confirmFunc('Обновить остатки всех товаров?')"
                       class="btn btn-default">Обновить остатки</a>
                </td>
                <td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
                    Обновление остатков всех товаров
                </td>
            </tr>
		</tbody>
	</table>

	<br>
	<h3>Наценки на категории</h3>
	<form method="post" action="{""|fn_url}">
		<label>
			<input type="text" name="cm[0]" placeholder="наценка в процентах" value="{$category.margin}" />
			Отсутствующий товар
		</label>

		{foreach from=$categories item="category"}
		<label>
			<input type="text" name="cm[{$category.category_id}]" placeholder="наценка в процентах" value="{$category.margin}" />
			{$category.category}
		</label>
		{/foreach}
		<input type="submit" name="dispatch[price_parser.update]" value="Submit">
	</form>
		
	<br />
	
	<br />
	<br /><br /><br />

{/capture}


{include file="common/mainbox.tpl" title=__("price_parser") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}

{** ad section **}