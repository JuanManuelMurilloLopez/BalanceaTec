#include <WiFi.h>

// Configuración WiFi
const char* ssid = "POCO X6 Pro 5G";
const char* password = "RedmiJuan";

//Obtención de la Dirección MAC del ESP32 (Device_ID)
//String deviceID = WiFi.macAddress();

void conectarWifi(){
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Conectando a WiFi...");
  while(WiFi.status() != WL_CONNECTED){
    Serial.print(".");
    delay(1000);
  }
  Serial.println("Conectado!");
}

void setup(){
    Serial.begin(115200);

    conectarWifi();

    
}

void loop(){
  Serial.println(WiFi.macAddress());
  delay(10000);
}