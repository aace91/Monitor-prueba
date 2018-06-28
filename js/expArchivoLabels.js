function getBarcodeLabelXmlEtiquetaExpediente() {
	var labelXml = '<?xml version="1.0" encoding="utf-8"?>\
		<DieCutLabel Version="8.0" Units="twips">\
			<PaperOrientation>Landscape</PaperOrientation>\
			<Id>LargeShipping</Id>\
			<IsOutlined>false</IsOutlined>\
			<PaperName>30256 Shipping</PaperName>\
			<DrawCommands>\
				<RoundRectangle X="0" Y="0" Width="3331" Height="5715" Rx="270" Ry="270" />\
			</DrawCommands>\
			<ObjectInfo>\
				<BarcodeObject>\
					<Name>txt_codebar</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>True</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<Text>12345</Text>\
					<Type>Code39</Type>\
					<Size>Medium</Size>\
					<TextPosition>None</TextPosition>\
					<TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
					<CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
					<TextEmbedding>None</TextEmbedding>\
					<ECLevel>0</ECLevel>\
					<HorizontalAlignment>Center</HorizontalAlignment>\
					<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />\
				</BarcodeObject>\
				<Bounds X="510" Y="2625" Width="4620" Height="555" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<ImageObject>\
					<Name>GRÁFICA</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<Image>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</Image>\
					<ScaleMode>Uniform</ScaleMode>\
					<BorderWidth>0</BorderWidth>\
					<BorderColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<HorizontalAlignment>Center</HorizontalAlignment>\
					<VerticalAlignment>Center</VerticalAlignment>\
				</ImageObject>\
				<Bounds X="336" Y="150" Width="840" Height="735" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">GRUPO ADUANERO DEL BRAVO, S.A. DE C.V.</String>\
							<Attributes>\
								<Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="1275" Y="465" Width="4125" Height="240" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO_1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Cliente:</String>\
							<Attributes>\
								<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="336" Y="1035" Width="2025" Height="270" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO__1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Impor/Expo:</String>\
							<Attributes>\
								<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="336" Y="1335" Width="2025" Height="270" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO___1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Pedimento:</String>\
							<Attributes>\
								<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="336" Y="1665" Width="2025" Height="270" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO____1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Referencia:</String>\
							<Attributes>\
								<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="336" Y="1980" Width="2025" Height="270" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO_____1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Factura (Cuenta):</String>\
							<Attributes>\
								<Font Family="Arial" Size="11" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="336" Y="2295" Width="2025" Height="270" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<ShapeObject Stroke="SolidLine">\
					<Name>FORMA_1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<ShapeType>HorizontalLine</ShapeType>\
					<LineWidth>15</LineWidth>\
					<LineAlignment>Center</LineAlignment>\
					<FillColor Alpha="0" Red="255" Green="255" Blue="255" />\
				</ShapeObject>\
				<Bounds X="1159" Y="1290" Width="4200" Height="15" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<ShapeObject Stroke="SolidLine">\
					<Name>FORMA__1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<ShapeType>HorizontalLine</ShapeType>\
					<LineWidth>15</LineWidth>\
					<LineAlignment>Center</LineAlignment>\
					<FillColor Alpha="0" Red="255" Green="255" Blue="255" />\
				</ShapeObject>\
				<Bounds X="1579" Y="1635" Width="3810" Height="15" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<ShapeObject Stroke="SolidLine">\
					<Name>FORMA___1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<ShapeType>HorizontalLine</ShapeType>\
					<LineWidth>15</LineWidth>\
					<LineAlignment>Center</LineAlignment>\
					<FillColor Alpha="0" Red="255" Green="255" Blue="255" />\
				</ShapeObject>\
				<Bounds X="1579" Y="1935" Width="3810" Height="15" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<ShapeObject Stroke="SolidLine">\
					<Name>FORMA____1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<ShapeType>HorizontalLine</ShapeType>\
					<LineWidth>15</LineWidth>\
					<LineAlignment>Center</LineAlignment>\
					<FillColor Alpha="0" Red="255" Green="255" Blue="255" />\
				</ShapeObject>\
				<Bounds X="1579" Y="2235" Width="3810" Height="15" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<ShapeObject Stroke="SolidLine">\
					<Name>FORMA_____1</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<ShapeType>HorizontalLine</ShapeType>\
					<LineWidth>15</LineWidth>\
					<LineAlignment>Center</LineAlignment>\
					<FillColor Alpha="0" Red="255" Green="255" Blue="255" />\
				</ShapeObject>\
				<Bounds X="2164" Y="2565" Width="3225" Height="15" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>txt_cliente</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Maquiladora</String>\
							<Attributes>\
								<Font Family="Arial" Size="14" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="1245" Y="1050" Width="4125" Height="240" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>txt_empo_expo</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Maquiladora</String>\
							<Attributes>\
								<Font Family="Arial" Size="14" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="1700.78742711807" Y="1365" Width="3615" Height="240" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>txt_pedimento</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Maquiladora</String>\
							<Attributes>\
								<Font Family="Arial" Size="16" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="1700.78742711807" Y="1665" Width="3570" Height="240" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>txt_referencia</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Maquiladora</String>\
							<Attributes>\
								<Font Family="Arial" Size="14" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="1700.78742711807" Y="1995" Width="3570" Height="238.110239796529" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>txt_cuenta</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Maquiladora</String>\
							<Attributes>\
								<Font Family="Arial" Size="14" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="2224" Y="2325" Width="3060" Height="238.110239796529" />\
			</ObjectInfo>\
		</DieCutLabel>';
		
	return labelXml;
}

function getBarcodeLabelXmlEtiquetaCaja() {
	var labelXml = '<?xml version="1.0" encoding="utf-8"?>\
		<DieCutLabel Version="8.0" Units="twips">\
			<PaperOrientation>Landscape</PaperOrientation>\
			<Id>LargeShipping</Id>\
			<IsOutlined>false</IsOutlined>\
			<PaperName>30256 Shipping</PaperName>\
			<DrawCommands>\
				<RoundRectangle X="0" Y="0" Width="3331" Height="5715" Rx="270" Ry="270" />\
			</DrawCommands>\
			<ObjectInfo>\
				<ImageObject>\
					<Name>GRÁFICA</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<Image>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</Image>\
					<ScaleMode>Uniform</ScaleMode>\
					<BorderWidth>0</BorderWidth>\
					<BorderColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<HorizontalAlignment>Center</HorizontalAlignment>\
					<VerticalAlignment>Center</VerticalAlignment>\
				</ImageObject>\
				<Bounds X="381" Y="150" Width="720" Height="720" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>TEXTO</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Left</HorizontalAlignment>\
					<VerticalAlignment>Middle</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">GRUPO ADUANERO DEL BRAVO SA DE CV</String>\
							<Attributes>\
								<Font Family="Arial" Size="48" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="1170" Y="420" Width="4290" Height="255" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<BarcodeObject>\
					<Name>txt_codebar</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<Text>919</Text>\
					<Type>Pdf417</Type>\
					<Size>Large</Size>\
					<TextPosition>None</TextPosition>\
					<TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
					<CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />\
					<TextEmbedding>None</TextEmbedding>\
					<ECLevel>0</ECLevel>\
					<HorizontalAlignment>Center</HorizontalAlignment>\
					<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />\
				</BarcodeObject>\
				<Bounds X="336" Y="1035" Width="5070" Height="1605" />\
			</ObjectInfo>\
			<ObjectInfo>\
				<TextObject>\
					<Name>txt_caja</Name>\
					<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />\
					<BackColor Alpha="0" Red="255" Green="255" Blue="255" />\
					<LinkedObjectName />\
					<Rotation>Rotation0</Rotation>\
					<IsMirrored>False</IsMirrored>\
					<IsVariable>False</IsVariable>\
					<GroupID>-1</GroupID>\
					<IsOutlined>False</IsOutlined>\
					<HorizontalAlignment>Center</HorizontalAlignment>\
					<VerticalAlignment>Top</VerticalAlignment>\
					<TextFitMode>ShrinkToFit</TextFitMode>\
					<UseFullFontHeight>True</UseFullFontHeight>\
					<Verticalized>False</Verticalized>\
					<StyledText>\
						<Element>\
							<String xml:space="preserve">Caja #919</String>\
							<Attributes>\
								<Font Family="Arial" Size="24" Bold="True" Italic="False" Underline="False" Strikeout="False" />\
								<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />\
							</Attributes>\
						</Element>\
					</StyledText>\
				</TextObject>\
				<Bounds X="336" Y="2589" Width="5019" Height="615" />\
			</ObjectInfo>\
		</DieCutLabel>';
		
	return labelXml;
}