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
					<a href="http://test/admin.php?dispatch=price_parser.manage&method=downloadPrices" 
						onclick="return confirmFunc('Вы действительно хотите обновить прайс листы?')" 
						class="btn btn-default">Обновить прайс листы</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Загрузка архивов с новыми прайс листами и их распаковка
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="http://test/admin.php?dispatch=price_parser.manage&method=clearDb" 
						onclick="return confirmFunc('Вы действительно хотите очистить БД?')" 
						class="btn btn-default">Очистить БД</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Удаление из БД всех категорий, товаров, характеристик и изображений товаров
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="http://test/admin.php?dispatch=price_parser.manage&method=fillDb" 
						onclick="return confirmFunc('Заполнить БД?')" 
						class="btn btn-default">Заполнить БД</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Заполнение БД категориями, товарами, изображениями и характеристиками
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="http://test/admin.php?dispatch=price_parser.manage&method=updateCategories" 
						onclick="return confirmFunc('Обновить категории?')" 
						class="btn btn-default">Обновить категории</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Обновление категорий
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="http://test/admin.php?dispatch=price_parser.manage&method=updateProducts" 
						onclick="return confirmFunc('Обновить товары?')" 
						class="btn btn-default">Обновить товары</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Обновление товаров
				</td>
			</tr>

			<tr style="border-bottom: 1px solid #D0D0D0;">
				<td style="width: 200px;padding-top: 10px;padding-bottom: 10px;">
					<a href="http://test/admin.php?dispatch=price_parser.manage&method=updatePrices" 
						onclick="return confirmFunc('Обновить цены всех товаров?')" 
						class="btn btn-default">Обновить цены</a>
				</td>
				<td style="font-size: 12px;color: #717171;padding-top: 10px;padding-bottom: 10px;">
					Обновление цен всех товаров
				</td>
			</tr>
		</tbody>
	</table>

{/capture}


{include file="common/mainbox.tpl" title=__("price_parser") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}

{** ad section **}