#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// Configuración WiFi
const char* ssid = "Poco X6 Pro 5G";
const char* password = "RedmiJuan";

//Archivo con el que se va a comunicar para enviar datos a la base
const char* sendDataURL = "http://192.168.56.1/BalanceaTec/Aduino/conexion_ESP32.php";

//Archivo con el que se va a comunicar para recibir datos de la base
const char* getLimitsURL = "http://192.168.56.1/BalanceaTec/Aduino/obtener_limit_values.php";

//Obtención de la Dirección MAC del ESP32 (Device_ID)
String deviceID = WiFi.macAddress();

//Inizialización de variables
float temperature = 25;
float humidity = 45;
float rotation_x = 1, rotation_y = 2, rotation_z = 3;
float acceleration_x= 4, acceleration_y = 5, acceleration_z = 6;

//Variables para los límites
float maxTemperature, minTemperature;
float maxHumidity, minHumidity;
float rotationTolerance;
float acelerationTolerance;

void conectarWifi(){
  WiFi.begin(ssid, password);
  Serial.print("Conectando a WiFi...");
  while(WiFi.status() != WL_CONNECTED){
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\n Conexión establecida.");
}

void enviarDatos(){
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;

    //Crear la solicitud HTTP POST
    http.begin(sendDataURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    //Datos a enviar
    String postData = "device_ID=" + deviceID +
                      "&temperature=" + String(temperature) +
                      "&humidity=" + String(humidity) +
                      "&rotation_x=" + String(rotation_x) +
                      "&rotation_y=" + String(rotation_y) +
                      "&rotation_z=" + String(rotation_z) +
                      "&acceleration_x=" + String(acceleration_x) +
                      "&acceleration_y=" + String(acceleration_y) +
                      "&acceleration_z=" + String(acceleration_z);
    int httpResponseCode = http.POST(postData);
    if(httpResponseCode > 0){
      Serial.println("Datos enviados correctamente: " + String(httpResponseCode));
      Serial.println("Respuesta del servidor: " + http.getString());
    }
    else{
      Serial.println("Error al enviar datos: " + String(httpResponseCode));
    }
    http.end();
  }
  else{
    Serial.println("WiFi no conectado.");
  }
}

void obtenerLimitValues() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(getLimitsURL) + "?device_ID=" + deviceID;
    http.begin(url);

    int httpResponseCode = http.GET();

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Límites recibidos: " + response);

      //Parsear el JSON recibido
      StaticJsonDocument<200> doc; // 200 -> Tamaño
      DeserializationError error = deserializeJson(doc, response);

      if (!error) {
        maxTemperature = doc["max_temperature"];
        minTemperature = doc["min_temperature"];
        maxHumidity = doc["max_humidity"];
        minHumidity = doc["min_humidity"];
        rotationTolerance = doc["rotation_tolerance"];
        acelerationTolerance = doc["aceleration_tolerance"];
        Serial.println("Límites aplicados correctamente:");
        Serial.println("Max Temp: " + String(maxTemperature));
        Serial.println("Min Temp: " + String(minTemperature));
        Serial.println("Max Humidity: " + String(maxHumidity));
        Serial.println("Min Humidity: " + String(minHumidity));
      } else {
        Serial.println("Error al parsear los límites: " + String(error.c_str()));
      }
    } else {
      Serial.println("Error al obtener límites: " + String(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi no conectado.");
  }
}

void setup() {
  Serial.begin(115200);

  conectarWifi();

  obtenerLimitValues();

}

void loop() {

  enviarDatos();
  delay(5000);

  //Tiempo para actualizar los límites (Actualmente: 2 minutos)
  int tiempo_actualizar_milisegundos = 120000;
  static unsigned long lastUpdate = 0;
  if (millis() - lastUpdate > tiempo_actualizar_milisegundos) {
    obtenerLimitValues();
    lastUpdate = millis();
  }
}
