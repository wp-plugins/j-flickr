<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<ul>
			<xsl:for-each select="//tag">
				<li>
					<xsl:value-of select="." />
					<xsl:if test="@count">Count: <xsl:value-of select="@count" /></xsl:if>
					<xsl:if test="@score">Score: <xsl:value-of select="@score" /></xsl:if>
					<xsl:if test="@id">ID: <xsl:value-of select="@id" /></xsl:if>
					<xsl:if test="@author">Author: <xsl:value-of select="@author" /></xsl:if>
					<xsl:if test="@authorname">Author name: <xsl:value-of select="@authorname" /></xsl:if>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>