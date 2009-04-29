<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<ul class="jflickrPhotoStrip">
			<xsl:for-each select="//photo">
				<li>
					<xsl:element name="a">
						<xsl:attribute name="href">http://flickr.com/photos/{{username}}/<xsl:value-of select="@id" /></xsl:attribute>

						<xsl:element name="img">
							<xsl:attribute name="alt"></xsl:attribute>
							<xsl:attribute name="src">http://farm<xsl:value-of select="@farm" />.static.flickr.com/<xsl:value-of select="@server" />/<xsl:value-of select="@id" />_<xsl:value-of select="@secret" />_s.jpg</xsl:attribute>
							<xsl:attribute name="title"><xsl:value-of select="@title" /></xsl:attribute>
							<xsl:attribute name="class">jFlickrPhoto</xsl:attribute>
						</xsl:element>
					</xsl:element>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>