<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">
 <% with $Station.TemplateVars %>
<h2>$Name, $Country</h2>
<img src="$WeatherIconURL" alt="$WeatherDescription" title1="$WeatherDescription"/>
<span class="temperature">{$TemperatureCurrent}&deg;C
<p>$WeatherDescription</p>
<table>
<tbody>
<tr><th>Wind</th><td>$WindSpeed km/h at {$WindDirection}&deg;</td></tr>
<tr><th>Cloudiness</th><td>{$CloudCoverPercentage}%</td></tr>
<tr><th>Pressure</th><td>$Pressure hpa</td></tr>
<tr><th>Humidity</th><td>{$Humidity}%</td></tr>
<tr><th>Sunrise</th><td>$Sunrise.Format(H:i)</td></tr>
<tr><th>Sunset</th><td>$Sunset.Format(H:i)</td></tr>
<tr><th>Map</th><td><a href="http://maps.google.com?q=$Latitude,$Longitude">$Latitude,$Longitude</a></td></tr>
<tr><th>OpenWeatherMap</th><td><a href="http://openweathermap.com/city/{$Station.OpenWeatherMapStationID}">$Name</a></td></tr>
</tbody>
</table>
<% end_with %>
		</div>
	</article>
		$Form
		$PageComments
</div>
