<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<ul class="jflickrPlaces">
			<xsl:for-each select="//place">
				<li>
					<xsl:value-of select="." /> (<xsl:value-of select="@latitude" />, <xsl:value-of select="@longitude" />)
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>