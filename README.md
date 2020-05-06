# web_task

PHP, OpenLayers, Postgres + PostGIS

Step 0: Setup

        * setup php environment
        * setup postgres with postgis
        * import database dump

Step 1: Data Analysis

        * provide an user interface to select a region 
        * calculate max, min, median and average for all points inside the selected region
        * output the calculated values in a table

Step 2: Data Visualization (JavaScript/OpenLayers)

        * display selected region as a vector layer with outlined borders
        * display all points inside the selected region as a vector layer with different colors for values above and below the median
