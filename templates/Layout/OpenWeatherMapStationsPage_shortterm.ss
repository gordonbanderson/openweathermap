<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">
 <% with $Station.TemplateVars %>
<h2>$Station.Name, $Station.Country - Short Term Forecast</h2>
<table id="upcomingWeather">
<tr>
<% loop $Forecasts %>
<td><img src="$WeatherIconURL" alt="$WeatherDescription"/></td>
<% end_loop %>
</tr>
<tr class="timeLabels">
<% loop $Forecasts %>
<td>$DateTime.Format(H:i)</td>
<% end_loop %>
</tr>
</table>
<h2>Temperature (&deg;C) </h2>
<canvas id="temperatureChart" class="weatherChart" height="100px"></canvas>

<h2>Rainfall (mm)</h2>
<canvas id="rainfallChart" class="weatherChart" height="100px"></canvas>

<h2>Humidity &amp; Cloud Cover (%)</h2>
<canvas id="cloudHumidityChart" class="weatherChart" height="100px"></canvas>
<% end_with %>
		</div>
	</article>
		$Form
		$PageComments
</div>
