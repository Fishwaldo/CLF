Option Explicit On 


Imports System.ComponentModel
Imports System.Drawing
Imports System.Windows.Forms
Imports System.Threading
Imports System.Net.Sockets

Public Class netcoms

    Private ActiveThreads As Thread
    Private ActiveThreadsState As Integer
    Private user As String
    Private host As String
    Private pass As String
    Private connected As Boolean
    Friend logmain

    Public Event loginok()
    Public Event disconnected(ByVal reason As String)

    Public Sub New()
        connected = False
    End Sub
    Public Property username() As String
        Get
            Return user
        End Get
        Set(ByVal Value As String)
            user = Value
        End Set
    End Property
    Public Property password() As String
        Get
            Return pass
        End Get
        Set(ByVal Value As String)
            pass = Value
        End Set
    End Property
    Public Property hostname() As String
        Get
            Return hostname
        End Get
        Set(ByVal Value As String)
            host = Value
        End Set
    End Property
    Public Sub connect(ByVal username As String, ByVal password As String, ByVal hostname As String)

        Dim i As Integer
        Dim ClientCount As Integer
        Dim ActiveThreadStart As ThreadStart
        user = username
        pass = password
        host = hostname

        'Create a ThreadStart object, passing the address of NewThread             
        ActiveThreadStart = New ThreadStart(AddressOf startclient)
        ActiveThreads = New Thread(ActiveThreadStart)
        ActiveThreads.Name = "tcpclient"
        ActiveThreads.Start()
        ActiveThreadsState = System.Threading.ThreadState.Running


    End Sub
    Private Sub dowrite(ByVal client As TcpClient, ByVal message As String)
        Dim Buffer() As Byte

        Buffer = System.Text.Encoding.Default.GetBytes(message.ToCharArray)

        client.GetStream().Write(Buffer, 0, Buffer.Length)

    End Sub
    Protected Sub processmessage(ByVal sock As TcpClient, ByVal message As String)
        Dim Bytes As Integer
        Dim Temp As String
        Dim cmd() As String
        Dim NewThread As Thread
        NewThread = System.Threading.Thread.CurrentThread


        cmd = message.Split
        Select Case (cmd(0).ToUpper)
            Case "LOGINOK"
                Console.WriteLine("Login was ok" & vbCrLf)
                SyncLock NewThread
                    RaiseEvent loginok()
                End SyncLock
        End Select
    End Sub

    Sub startclient()
        Dim NewThread As Thread
        Dim ThreadName As String
        Dim Client As TcpClient
        Dim InBuff(100) As Byte
        Dim Temp As String

        NewThread = System.Threading.Thread.CurrentThread
        ThreadName = NewThread.Name

        Client = New TcpClient()

        Try
            Client.Connect(host, 9105)
        Catch e As Exception
            SyncLock NewThread
                Console.WriteLine("Connection to server failed with return code: " & e.Message)
            End SyncLock
            RaiseEvent disconnected(e.Message)
            Exit Sub
        End Try


        dowrite(Client, "Login: " & user & " Pass " & pass)
        While True
            While Not Client.GetStream.DataAvailable()
                Application.DoEvents()
            End While

            If Client.GetStream.DataAvailable() Then
                Client.GetStream().Read(InBuff, 0, InBuff.Length)
                Temp = System.Text.Encoding.Default.GetString(InBuff)
                SyncLock NewThread
                    Console.WriteLine(Temp & vbCrLf)
                End SyncLock
                processmessage(Client, Temp)
            End If

        End While
        Client.Close()
        RaiseEvent disconnected("ended")
        ActiveThreads.Abort()
    End Sub
End Class