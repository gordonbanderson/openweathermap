<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">
asdfsadf
		 <% with $Station.TemplateVars %>
<h2>$Station.Name, $Station.Country</h2>
<table>
<tbody>
<% loop $Forecasts %>
<tr class="longTermWeatherForecast">
<td>Date</td>
<td><img src="$WeatherIconURL" alt="$WeatherDescription" title="$WeatherDescription"/></td>
<td>
<span class="daytemp">$TemperatureDay</span>
<span class="nighttemp">$TemperatureNight</span>
$WeatherDescription<br/>
$WindSpeed<br/>
Clouds: $CloudCoverage, $Pressure hpa
</td>

</tr>
<% end_loop %>
</tbody>
</table>
<% end_with %>
</div>
	</article>
		$Form
		$PageComments
</div>
