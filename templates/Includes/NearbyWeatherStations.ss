<table>
<tr>
<th>Name</th>
<th>Distance</th>
<th>Location</th>
</tr>

<% loop $WeatherStations %>
<tr>
<td>$Name</td>
<td>$Distance</td>
<td><a href="http://maps.google.com?q={$Lat},{$Lon}" target="_map">Google Map</a></td>
<% end_loop %>
</table>
