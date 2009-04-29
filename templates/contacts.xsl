<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<ul class="jflickrContacts">
			<xsl:for-each select="//contact">
				<li>
					<xsl:element name="a">
						<xsl:attribute name="href"><xsl:value-of select="@username" /></xsl:attribute>
						<xsl:value-of select="@username" />
					</xsl:element>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>