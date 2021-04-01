//+------------------------------------------------------------------+
//|                                                    MySQL-003.mq5 |
//|                                   Copyright 2014, Eugene Lugovoy |
//|                                              http://www.mql5.com |
//| Inserting data with multi-statement (DEMO)                       |
//+------------------------------------------------------------------+
#property copyright "Copyright 2014, Eugene Lugovoy."
#property link      "http://www.mql5.com"
#property version   "1.00"
#property strict

#include <MQLMySQL.mqh>

//+------------------------------------------------------------------+
//| Script program start function                                    |
//+------------------------------------------------------------------+
int connectDB(int db)
  {

   string INI;
   string Host, User, Password, Database, Socket; // database credentials
   int Port,ClientFlag;

   Print(MySqlVersion());

   INI = "C:\\Users\\Cleyverson\\AppData\\Roaming\\MetaQuotes\\Terminal\\D0E8209F77C8CF37AD8BF550E51FF075\\MQL5\\Scripts\\MyConnection.ini";

// reading database credentials from INI file
   Host = ReadIni(INI, "MYSQL", "Host");
   User = ReadIni(INI, "MYSQL", "User");
   Password = ReadIni(INI, "MYSQL", "Password");
   Database = ReadIni(INI, "MYSQL", "Database");
   Port     = (int)StringToInteger(ReadIni(INI, "MYSQL", "Port"));
   Socket   = ReadIni(INI, "MYSQL", "Socket");
   ClientFlag = CLIENT_MULTI_STATEMENTS; //(int)StringToInteger(ReadIni(INI, "MYSQL", "ClientFlag"));

   Print("Host: ",Host, ", User: ", User, ", Database: ",Database);

// open database connection
   Print("Connecting...");

   db = MySqlConnect(Host, User, Password, Database, Port, Socket, ClientFlag);

   if(db == -1)
     {
      Print("Connection failed! Error: "+MySqlErrorDescription);
     }
   else
     {
      Print("Connected! DBID#",db);
     }

   return db;
  }

//+------------------------------------------------------------------+
//|                                                                  |
//+------------------------------------------------------------------+
void insertData(int db, string query)
  {

// Inserting data 1 row
   if(MySqlExecute(db, query))
     {
      //Print ("Succeeded: ", query);
     }
   else
     {
      //Print ("Error: ", MySqlErrorDescription);
      //Print ("Query: ", query);
     }

  }

//+------------------------------------------------------------------+
//|                                                                  |
//+------------------------------------------------------------------+
int existRow(int db, string query)
  {

   int    i,Cursor,Rows;

   int      vId;
   string   vCode;
   datetime vStartTime;

   //Print("SQL> ", query);
   Cursor = MySqlCursorOpen(db, query);

   if(Cursor >= 0)
     {

      Rows = MySqlCursorRows(Cursor);

      MySqlCursorClose(Cursor); // NEVER FORGET TO CLOSE CURSOR !!!

      int result = 0;
      if(Rows >= 1)
        {
         result = 1;
        }

      MySqlCursorClose(Cursor); // NEVER FORGET TO CLOSE CURSOR !!!

      return result;

     }
   else
     {
      Print("Cursor opening failed. Error: ", MySqlErrorDescription);
      return 2;
     }
  }

//+------------------------------------------------------------------+
//|                                                                  |
//+------------------------------------------------------------------+
void closeConnection(int db)
  {

   MySqlDisconnect(db);
   Print("Disconnected. Script done!");

  }

//+------------------------------------------------------------------+
