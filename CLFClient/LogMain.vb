Public Class LogMain
    Inherits System.Windows.Forms.Form
    Private hostarray As New ArrayList()
    Private messagearray As New ArrayList()
    Private myMessageSet As DataSet
    Friend netcoms
#Region " Windows Form Designer generated code "

    Public Sub New()
        MyBase.New()

        'This call is required by the Windows Form Designer.
        InitializeComponent()

        'Add any initialization after the InitializeComponent() call

    End Sub

    'Form overrides dispose to clean up the component list.
    Protected Overloads Overrides Sub Dispose(ByVal disposing As Boolean)
        If disposing Then
            If Not (components Is Nothing) Then
                components.Dispose()
            End If
        End If
        MyBase.Dispose(disposing)
    End Sub

    'Required by the Windows Form Designer
    Private components As System.ComponentModel.IContainer

    'NOTE: The following procedure is required by the Windows Form Designer
    'It can be modified using the Windows Form Designer.  
    'Do not modify it using the code editor.
    Friend WithEvents TVHostlist As System.Windows.Forms.TreeView
    Friend WithEvents RTMessages As System.Windows.Forms.DataGrid
    Friend WithEvents Messages As System.Windows.Forms.DataGridTableStyle
    Friend WithEvents DateF As System.Windows.Forms.DataGridTextBoxColumn
    Friend WithEvents TimeF As System.Windows.Forms.DataGridTextBoxColumn
    Friend WithEvents HostF As System.Windows.Forms.DataGridTextBoxColumn
    Friend WithEvents SevF As System.Windows.Forms.DataGridTextBoxColumn
    Friend WithEvents FacF As System.Windows.Forms.DataGridTextBoxColumn
    Friend WithEvents MsgF As System.Windows.Forms.DataGridTextBoxColumn
    Friend WithEvents StatusBar1 As System.Windows.Forms.StatusBar
    <System.Diagnostics.DebuggerStepThrough()> Private Sub InitializeComponent()
        Me.TVHostlist = New System.Windows.Forms.TreeView()
        Me.RTMessages = New System.Windows.Forms.DataGrid()
        Me.Messages = New System.Windows.Forms.DataGridTableStyle()
        Me.DateF = New System.Windows.Forms.DataGridTextBoxColumn()
        Me.TimeF = New System.Windows.Forms.DataGridTextBoxColumn()
        Me.HostF = New System.Windows.Forms.DataGridTextBoxColumn()
        Me.FacF = New System.Windows.Forms.DataGridTextBoxColumn()
        Me.SevF = New System.Windows.Forms.DataGridTextBoxColumn()
        Me.MsgF = New System.Windows.Forms.DataGridTextBoxColumn()
        Me.StatusBar1 = New System.Windows.Forms.StatusBar()
        CType(Me.RTMessages, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.SuspendLayout()
        '
        'TVHostlist
        '
        Me.TVHostlist.ImageIndex = -1
        Me.TVHostlist.Name = "TVHostlist"
        Me.TVHostlist.SelectedImageIndex = -1
        Me.TVHostlist.Size = New System.Drawing.Size(128, 344)
        Me.TVHostlist.TabIndex = 0
        '
        'RTMessages
        '
        Me.RTMessages.DataMember = ""
        Me.RTMessages.HeaderForeColor = System.Drawing.SystemColors.ControlText
        Me.RTMessages.Location = New System.Drawing.Point(136, 0)
        Me.RTMessages.Name = "RTMessages"
        Me.RTMessages.ReadOnly = True
        Me.RTMessages.Size = New System.Drawing.Size(448, 312)
        Me.RTMessages.TabIndex = 2
        Me.RTMessages.TableStyles.AddRange(New System.Windows.Forms.DataGridTableStyle() {Me.Messages})
        '
        'Messages
        '
        Me.Messages.DataGrid = Me.RTMessages
        Me.Messages.GridColumnStyles.AddRange(New System.Windows.Forms.DataGridColumnStyle() {Me.DateF, Me.TimeF, Me.HostF, Me.FacF, Me.SevF, Me.MsgF})
        Me.Messages.HeaderForeColor = System.Drawing.SystemColors.ControlText
        Me.Messages.MappingName = ""
        Me.Messages.ReadOnly = True
        '
        'DateF
        '
        Me.DateF.Format = ""
        Me.DateF.FormatInfo = Nothing
        Me.DateF.HeaderText = "Date"
        Me.DateF.MappingName = "Date"
        Me.DateF.ReadOnly = True
        Me.DateF.Width = 75
        '
        'TimeF
        '
        Me.TimeF.Format = ""
        Me.TimeF.FormatInfo = Nothing
        Me.TimeF.HeaderText = "Time"
        Me.TimeF.MappingName = ""
        Me.TimeF.ReadOnly = True
        Me.TimeF.Width = 75
        '
        'HostF
        '
        Me.HostF.Format = ""
        Me.HostF.FormatInfo = Nothing
        Me.HostF.HeaderText = "Host"
        Me.HostF.MappingName = "HostID"
        Me.HostF.NullText = "<UNKNOWN>"
        Me.HostF.ReadOnly = True
        Me.HostF.Width = 75
        '
        'FacF
        '
        Me.FacF.Format = ""
        Me.FacF.FormatInfo = Nothing
        Me.FacF.HeaderText = "Facility"
        Me.FacF.MappingName = "Fac"
        Me.FacF.ReadOnly = True
        Me.FacF.Width = 75
        '
        'SevF
        '
        Me.SevF.Format = ""
        Me.SevF.FormatInfo = Nothing
        Me.SevF.HeaderText = "Severity"
        Me.SevF.MappingName = "Sev"
        Me.SevF.ReadOnly = True
        Me.SevF.Width = 75
        '
        'MsgF
        '
        Me.MsgF.Format = ""
        Me.MsgF.FormatInfo = Nothing
        Me.MsgF.HeaderText = "Message"
        Me.MsgF.MappingName = "Message"
        Me.MsgF.ReadOnly = True
        Me.MsgF.Width = 75
        '
        'StatusBar1
        '
        Me.StatusBar1.Location = New System.Drawing.Point(0, 326)
        Me.StatusBar1.Name = "StatusBar1"
        Me.StatusBar1.Size = New System.Drawing.Size(592, 16)
        Me.StatusBar1.TabIndex = 3
        '
        'LogMain
        '
        Me.AutoScaleBaseSize = New System.Drawing.Size(5, 13)
        Me.ClientSize = New System.Drawing.Size(592, 342)
        Me.Controls.AddRange(New System.Windows.Forms.Control() {Me.StatusBar1, Me.TVHostlist, Me.RTMessages})
        Me.Name = "LogMain"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterParent
        Me.Text = "System Messages"
        Me.WindowState = System.Windows.Forms.FormWindowState.Maximized
        CType(Me.RTMessages, System.ComponentModel.ISupportInitialize).EndInit()
        Me.ResumeLayout(False)

    End Sub

#End Region

    Private Sub LogMain_Resize(ByVal sender As Object, ByVal e As System.EventArgs) Handles MyBase.Resize
        RTMessages.Width = Me.Width - TVHostlist.Width
        RTMessages.Height = Me.Height - StatusBar1.Height
        TVHostlist.Height = Me.Height - StatusBar1.Height
    End Sub

    Private Sub CreateMyStatusBar()
        ' Create a StatusBar control.
        ' Create two StatusBarPanel objects to display in the StatusBar.
        Dim panel1 As New StatusBarPanel()
        Dim panel2 As New StatusBarPanel()
        Dim panel3 As New StatusBarPanel()

        ' Display the first panel with a sunken border style.
        panel1.BorderStyle = StatusBarPanelBorderStyle.Sunken
        ' Initialize the text of the panel.
        panel1.Text = "Ready..."
        ' Set the AutoSize property to use all remaining space on the StatusBar.
        'panel1.Width = 100
        panel1.AutoSize = StatusBarPanelAutoSize.Spring
        ' Display the second panel with a raised border style.
        panel2.BorderStyle = StatusBarPanelBorderStyle.Raised
        ' Create ToolTip text that displays the current time.
        panel2.ToolTipText = System.DateTime.Now.ToShortTimeString()
        ' Set the text of the panel to the current date.
        panel2.Text = System.DateTime.Today.ToLongDateString()
        ' Set the AutoSize property to size the panel to the size of the contents.
        panel2.AutoSize = StatusBarPanelAutoSize.Contents
        panel3.BorderStyle = StatusBarPanelBorderStyle.Sunken
        panel3.Text = "0/0 Records Displayed"
        panel3.AutoSize = StatusBarPanelAutoSize.Contents


        StatusBar1.Panels.Add(panel1)
        StatusBar1.Panels.Add(panel3)
        StatusBar1.Panels.Add(panel2)


        ' Display panels in the StatusBar control.
        StatusBar1.ShowPanels = True

        ' Add both panels to the StatusBarPanelCollection of the StatusBar.         

    End Sub


    Private Sub LogMain_Load(ByVal sender As Object, ByVal e As System.EventArgs) Handles MyBase.Load
        CreateMyStatusBar()
        Dim x As Integer
        For x = 0 To 20
            hostarray.Add(New String("Host" + x.ToString()))
        Next
        For x = 0 To 30
            messagearray.Add(New String("message" + x.ToString()))
        Next
        TVHostlist.BeginUpdate()
        TVHostlist.Nodes.Clear()
        Dim host As String
        For Each host In hostarray
            TVHostlist.Nodes.Add(New TreeNode(host))
        Next
        TVHostlist.EndUpdate()
        Dim message As String

        For Each message In messagearray
            RTMessages.Text = message
        Next
        SetUp()
        RTMessages.Update()
    End Sub

    Private Sub SetUp()
        ' Create a DataSet with two tables and one relation.
        MakeDataSet()
        ' Bind the DataGrid to the DataSet. The dataMember
        ' specifies that the Customers table should be displayed.
        RTMessages.SetDataBinding(myMessageSet, "Message")

    End Sub



    Private Sub RTMessages_MouseUp(ByVal sender As Object, ByVal e As MouseEventArgs)
        ' Create a HitTestInfo object using the HitTest method.
        ' Get the DataGrid by casting sender.
        Dim myGrid As DataGrid = CType(sender, DataGrid)
        Dim myHitInfo As DataGrid.HitTestInfo = RTMessages.HitTest(e.X, e.Y)
        Console.WriteLine(myHitInfo)
        Console.WriteLine(myHitInfo.Type)
        Console.WriteLine(myHitInfo.Row)
        Console.WriteLine(myHitInfo.Column)
    End Sub

    ' Create a DataSet with two tables and populate it.
    Private Sub MakeDataSet()
        ' Create a DataSet.
        StatusBar1.Panels(0).Text = "Loading..."
        myMessageSet = New DataSet("myMessages")

        ' Create two DataTables.
        Dim thost As New DataTable("Hosts")
        Dim tmessage As New DataTable("Message")
        tmessage.Select()
        ' Create two columns, and add them to the first table.
        Dim tHostID As New DataColumn("HostID", GetType(Integer))
        Dim THostName As New DataColumn("HostName")
        thost.Columns.Add(tHostID)
        thost.Columns.Add(THostName)

        ' Create three columns, and add them to the second table.
        Dim mID As New DataColumn("MsgID", GetType(Integer))
        Dim mHostID As New DataColumn("HostID", GetType(Integer))
        Dim mDate As New DataColumn("Date", GetType(DateTime))
        Dim mSev As New DataColumn("Sev", GetType(Integer))
        Dim mFac As New DataColumn("Fac", GetType(Integer))
        Dim mMsg As New DataColumn("Message")


        tmessage.Columns.Add(mID)
        tmessage.Columns.Add(mHostID)
        tmessage.Columns.Add(mDate)
        tmessage.Columns.Add(mSev)
        tmessage.Columns.Add(mFac)
        tmessage.Columns.Add(mMsg)
        ' Add the tables to the DataSet.

        myMessageSet.Tables.Add(thost)
        myMessageSet.Tables.Add(tmessage)

        ' Create a DataRelation, and add it to the DataSet.
        Dim dr As New DataRelation("msgtohost", tHostID, mHostID)
        myMessageSet.Relations.Add(dr)

        ' Populates the tables. For each customer and order, 
        ' creates two DataRow variables. 
        Dim newRow1 As DataRow
        Dim newRow2 As DataRow

        ' Create three customers in the Customers Table.
        Dim i As Integer
        For i = 1 To 10
            newRow1 = thost.NewRow()
            newRow1("HostID") = i
            newRow1("hostname") = "Host" + i.ToString
            ' Add the row to the Customers table.
            thost.Rows.Add(newRow1)
        Next i
        Dim k As Integer
        k = 1
        ' For each customer, create five rows in the Orders table.
        For i = 1 To 10
            Dim j As Integer
            For j = 1 To 12
                newRow2 = tmessage.NewRow()
                newRow2("msgID") = k
                newRow2("HostID") = i
                newRow2("Date") = New DateTime(2001, i, j)
                newRow2("sev") = 1
                newRow2("fac") = 1
                newRow2("message") = "This is the " + ((i * j).ToString) + " Message"
                ' Add the row to the Orders table.
                tmessage.Rows.Add(newRow2)
                k = k + 1
            Next j
        Next i
        Dim y As Integer
        y = tmessage.Rows.Count
        StatusBar1.Panels(1).Text = (k - 1).ToString + "/" + y.ToString + " Records Displayed"
        StatusBar1.Panels(0).Text = "Ready..."

    End Sub

    Private Sub StatusBar1_PanelClick(ByVal sender As System.Object, ByVal e As System.Windows.Forms.StatusBarPanelClickEventArgs) Handles StatusBar1.PanelClick

    End Sub
End Class
