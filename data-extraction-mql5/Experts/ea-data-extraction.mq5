//+------------------------------------------------------------------+
//|                                                          EA1.mq5 |
//|                        Copyright 2020, MetaQuotes Software Corp. |
//|                                             https://www.mql5.com |
//+------------------------------------------------------------------+
#property copyright "Copyright 2020, MetaQuotes Software Corp."
#property link "https://www.mql5.com"
#property version "1.00"

#include <MQLMySQL.mqh>
#include <MySQLFunc.mqh>

int db; // database identifier

//+------------------------------------------------------------------+
//| Expert initialization function                                   |
//+------------------------------------------------------------------+
int OnInit()
{
    db = connectDB(db);
    return (INIT_SUCCEEDED);
}
//+------------------------------------------------------------------+
//| Expert deinitialization function                                 |
//+------------------------------------------------------------------+
void OnDeinit(const int reason)
{
    closeConnection(db);
}
//+------------------------------------------------------------------+
//| Expert tick function                                             |
//+------------------------------------------------------------------+
void OnTick()
{

   //"KLBN11","EQTL3","UGPA3","CSNA3","RAIL3","BPAC11","BRDT3","BBDC3","RADL3","GGBR4","LREN3","RENT3","BBAS3","NTCO3","JBSS3","ITSA4","GNDI3","SUZB3","MGLU3","WEGE3","ABEV3","PETR3",
    string symbols[]={"B3SA3","PETR4","BBDC4","ITUB4","VALE3"};
    
    for(int i=0;i<ArraySize(symbols);i++) {
   
      string symbol = symbols[i];
   
      StringReplace(symbol,".","");
    
    //m0(symbol);
    
      mx(symbol);
   }
}

void m0(string symbol) {

   ENUM_TIMEFRAMES period = PERIOD_M1;

    MqlRates rates[1];
    if (CopyRates(symbol, period, 0, 1, rates) != 1) { /*error processing */};

    double open = rates[0].open;
    double high = rates[0].high;
    double low = rates[0].low;
    double close = rates[0].close;
    
    int trades_qty = rates[0].tick_volume;
    int volume_qty = vol_filter(symbol, period);
    
    string date;
    string time;
    string id;
    
    //date = TimeToString(TimeCurrent(), TIME_DATE | TIME_SECONDS);
    date = TimeCurrent();
    time = date;
    id = date;

   string minute = TimeToString(rates[0].time, TIME_DATE | TIME_MINUTES);

    add_by_tf_m0((int) id, minute, "M0", symbol, open, high, low, close, trades_qty, volume_qty);
    

}

void mx(string symbol) {

   //PERIOD_D1, PERIOD_H4, PERIOD_H1, PERIOD_M30, PERIOD_M15, PERIOD_M5, PERIOD_M1
   ENUM_TIMEFRAMES periods[] = {PERIOD_H1, PERIOD_M30, PERIOD_M15, PERIOD_M5}; 


   for(int i=0;i<ArraySize(periods);i++)
     {
      
   ENUM_TIMEFRAMES period = periods[i];
      

    MqlRates rates[1];
    if (CopyRates(symbol, period, 0, 1, rates) != 1) { /*error processing */};

    double open = rates[0].open;
    double high = rates[0].high;
    double low = rates[0].low;
    double close = rates[0].close;
    
    int trades_qty = rates[0].tick_volume;
    int volume_qty = vol_filter(symbol, period);
    
    string date;
    string time;
    string id;

    date = TimeToString(rates[0].time, TIME_DATE | TIME_MINUTES);
    time = rates[0].time;
    id = rates[0].time;
    
    string minute = TimeToString(rates[0].time, TIME_DATE | TIME_MINUTES);
    
    add_by_tf((int) id, minute, tf(period), symbol, open, high, low, close, trades_qty, volume_qty);      
     }



    
}

int vol_filter(string symbol, ENUM_TIMEFRAMES period)
   {
    int volumes = iVolumes(symbol, period,VOLUME_REAL);
    double vol_buffer[];
    int result;
    result = CopyBuffer( volumes ,0,0,1, vol_buffer );
    if (result == -1)
      return (0);
    else 
      result = vol_buffer[0];
      return (result);
         
         
   }

void add_by_tf_m0(int id, string minute, string timeframe, string symbol, double open, double high, double low, double close, int trades_qty, int volume_qty) {

    string query = "";

    query = "SELECT id FROM graphs WHERE id=" + id + " AND symbol='" + symbol + "' AND timeframe='" + timeframe + "'";

    int existRow = existRow(db, query);

    if (existRow == 2) {
        printf("error existRow");
    }
    else {

        //printf("existRow - "+ existRow);
        
        if (existRow == 0) {
            query = "INSERT INTO `graphs` (id, minute, timeframe, symbol, open, high, low, close, trades_qty, volume_qty) VALUES ('" + id + "', '" + minute + "', '" + timeframe + "', '" + symbol + "', " + open + ", " + high + ", " + low + ", " + close + ", " + trades_qty + ", " + volume_qty + ")";
            insertData(db, query);
        }
        else {
            printf("error existRow");
        }
    }
}


void add_by_tf(int id, string minute, string timeframe, string symbol, float open, float high, float low, float close, int trades_qty, int volume_qty) {

    string query = "";

    query = "SELECT id FROM graphs WHERE id=" + id + " AND symbol='" + symbol + "' AND timeframe='" + timeframe + "'";

    int existRow = existRow(db, query);

    if (existRow == 2) {
        printf("error existRow");
    }
    else {

        //printf("existRow - "+ existRow);
        
        if (existRow == 0) {
            query = "INSERT INTO `graphs` (id, minute, timeframe, symbol, open, high, low, close, trades_qty, volume_qty) VALUES ('" + id + "', '" + minute + "', '" + timeframe + "', '" + symbol + "', " + open + ", " + high + ", " + low + ", " + close + ", " + trades_qty + ", " + volume_qty + ")";
            insertData(db, query);
        }
        else {
            query = "UPDATE `graphs` SET open=" + open + ", high=" + high + ", low=" + low + ", close=" + close + ", trades_qty=" + trades_qty + ", volume_qty=" + volume_qty + ", showed=0 WHERE id=" + id + " AND symbol='" + symbol + "' AND timeframe='" + timeframe + "'";
            insertData(db, query);
        }
    }
}


//+------------------------------------------------------------------+

string tf(ENUM_TIMEFRAMES period)

{

    switch (period)

    {

    case PERIOD_M1:
        return ("M1");

    case PERIOD_M5:
        return ("M5");

    case PERIOD_M15:
        return ("M15");

    case PERIOD_M30:
        return ("M30");

    case PERIOD_H1:
        return ("H1");

    case PERIOD_H4:
        return ("H4");

    case PERIOD_D1:
        return ("D1");

    case PERIOD_W1:
        return ("W1");

    case PERIOD_MN1:
        return ("MN1");

    default:
        return ("Unknown timeframe");
    }
}
