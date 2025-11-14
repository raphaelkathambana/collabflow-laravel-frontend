@echo off
echo Starting Laravel Queue Worker...
echo This worker processes background jobs like AI task generation.
echo Keep this window open while using CollabFlow.
echo.
echo Press Ctrl+C to stop the worker.
echo.

"C:\Users\maya1\.config\herd\bin\php.bat" artisan queue:work --tries=3 --timeout=250 --sleep=3
