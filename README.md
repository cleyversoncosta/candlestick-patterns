# Candlestick Patterns

Neste projeto foram mapeados 55 padrões de candle de acordo com sua relevância (low / moderate / high) e tipo de sinal (reversal / continuation).

Cada um dos padrões foi verificado utilizando a bilbioteca [PHP Trader](https://www.php.net/manual/en/ref.trader.php) e plotada em gráfico utilizando a biblioteca Javascript [Anychart Candlestick](https://www.anychart.com/pt/products/anystock/gallery/Stock_Chart_Types/Candlestick_Chart.php).


**Com este sistema você pode:**

1. Verificar a formação de candles com base em dados históticos
2. Usar a extração de dados em [MQL5 Metatrader](https://www.mql5.com/pt/docs) e fazer a análise em tempo real
3. Usar como simulador para estudar as formações de candle

**O que você encontra neste projeto:**

1. [data-extraction-mql5](https://github.com/cleyversoncosta/candlestick-patterns/tree/main/data-extraction-mql5) - todos os arquivos necessários para fazer a extração de dados históricos e em tempo real utilizando MQL5 e Metatrader
2. [database-sql](https://github.com/cleyversoncosta/candlestick-patterns/tree/main/database-sql) - arquivo SQL para criar seu banco de dados MySQL já com dados históricos para teste
3. [laravel-system](https://github.com/cleyversoncosta/candlestick-patterns/tree/main/laravel-system) - sistema que gera e analisa os gráficos

Detalhes podem ser vistos na imagem abaixo

![Candlestick Patterns](https://github.com/cleyversoncosta/candlestick-patterns/blob/main/images/1.png)

