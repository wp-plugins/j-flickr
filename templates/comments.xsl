<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<ul class="jflickrComments">
			<xsl:for-each select="//comment">
				<li>
					<span class="author"><xsl:value-of select="@authorname" /><xsl:if test="@author"> [<xsl:value-of select="@author" />]</xsl:if></span>
						@ <span class="date"><xsl:value-of select="@datecreate" /></span><br />
					<p><xsl:value-of select="." /></p>

					<xsl:element name="a">
						<xsl:attribute name="href"><xsl:value-of select="@permalink" /></xsl:attribute>
						<xsl:attribute name="class">permalink</xsl:attribute>
						Permalink to this comment
					</xsl:element>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>