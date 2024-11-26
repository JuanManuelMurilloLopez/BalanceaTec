//Librerías conexiones
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

//Librerías sensores
#include <LiquidCrystal.h>
#include "DHT.h"
#include <Wire.h>

//Configuración WiFi
const char* ssid = "POCO X6 Pro 5G";
const char* password = "RedmiJuan";

//Archivo con el que se va a comunicar para enviar datos a la base
const char* sendDataURL = "http://192.168.250.5/IoT/BalanceaTec/BalanceaTec/xampp_esp32/conexion_ESP32.php";

//Archivo con el que se va a comunicar para recibir datos de la base
const char* getLimitsURL = "http://192.168.250.5/IoT/BalanceaTec/BalanceaTec/xampp_esp32/obtener_limit_values.php";

//Obtención de la Dirección MAC del ESP32 (Device_ID)
String deviceID;

//Configuración del DHT11
#define DHTPIN 4 //Pin DHT11
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

//Configuración del MPU6050
const int MPU_ADDR = 0x68; //Dirección I2C del MPU6050
int16_t aceleration_x, aceleration_y, aceleration_z;     //Aceleración en X, Y, Z
float baseAngleX = 0, baseAngleY = 0, baseAngleZ = 0; //Referencias iniciales

//Configuración del Motor
#define MOTOR_IN1 2  //Pin IN1 del L293D
#define MOTOR_IN2 14 //Pin IN2 del L293D

//Inizialización de variables
float temperature;
float humidity;
float rotation_x = 1, rotation_y = 2, rotation_z = 3;

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
  deviceID = WiFi.macAddress();
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
                      "&aceleration_x=" + String(aceleration_x) +
                      "&aceleration_y=" + String(aceleration_y) +
                      "&aceleration_z=" + String(aceleration_z);
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
    http.setTimeout(10000);

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
      Serial.println("Error al obtener límites: " + http.errorToString(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi no conectado.");
  }
}

void setup() {
  Serial.begin(115200);
  Serial.println(deviceID);
  conectarWifi();

  //Inicialización del DHT11
  dht.begin();
  Serial.println("DHT11 inicializado");

  //Inicialización del LCD
  lcd.begin(16, 2); //LCD de 16 columnas y 2 filas
  lcd.print("Iniciando...");
  delay(2000);
  lcd.clear();

  obtenerLimitValues();

  //Inicialización del MPU6050
  Wire.begin(25, 26); //SDA = GPIO 25, SCL = GPIO 26
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x6B); //Registro de power management
  Wire.write(0);    //Activar el MPU6050
  Wire.endTransmission(true);

  //Configuración de pines del motor
  pinMode(MOTOR_IN1, OUTPUT);
  pinMode(MOTOR_IN2, OUTPUT);
  digitalWrite(MOTOR_IN1, LOW);
  digitalWrite(MOTOR_IN2, LOW);

  //Calcular ángulos base para inicializar en 0 grados
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x3B); //Primer registro de datos (AcX)
  Wire.endTransmission(false);
  Wire.requestFrom(MPU_ADDR, 6, true);
  int16_t baseAcX = Wire.read() << 8 | Wire.read();
  int16_t baseAcY = Wire.read() << 8 | Wire.read();
  int16_t baseAcZ = Wire.read() << 8 | Wire.read();
  baseAngleX = atan2(baseAcY, baseAcZ) * 180 / PI;
  baseAngleY = atan2(baseAcX, baseAcZ) * 180 / PI;
  baseAngleZ = atan2(baseAcX, baseAcY) * 180 / PI;

  Serial.println("Ángulos inicializados en 0 grados:");
  Serial.print("Base AngleX: ");
  Serial.println(baseAngleX);
  Serial.print("Base AngleY: ");
  Serial.println(baseAngleY);
  Serial.print("Base AngleZ: ");
  Serial.println(baseAngleZ);
}

void loop() {
  //Leer los datos de temperatura y humedad
  temperature = dht.readTemperature();
  humidity = dht.readHumidity();
  Serial.println("Dirección MAC del dispositivo: " + deviceID);
  //Leer datos del MPU6050
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x3B); // Primer registro de datos (AcX)
  Wire.endTransmission(false);
  Wire.requestFrom(MPU_ADDR, 6, true);

  AcX = Wire.read() << 8 | Wire.read();
  AcY = Wire.read() << 8 | Wire.read();
  AcZ = Wire.read() << 8 | Wire.read();

  // Calcular ángulos de inclinación con referencia a los valores base
  float angleX = atan2(AcY, AcZ) * 180 / PI - baseAngleX;
  float angleY = atan2(AcX, AcZ) * 180 / PI - baseAngleY;

  // Controlar motor según el ángulo
  if (angleX > 10) {
    // Gira en una dirección
    digitalWrite(MOTOR_IN1, HIGH);
    digitalWrite(MOTOR_IN2, LOW);
  } else if (angleX < -10) {
    // Gira en la dirección opuesta
    digitalWrite(MOTOR_IN1, LOW);
    digitalWrite(MOTOR_IN2, HIGH);
  } else {
    // Detiene el motor
    digitalWrite(MOTOR_IN1, LOW);
    digitalWrite(MOTOR_IN2, LOW);
  }

  // Mostrar datos en el LCD
  lcd.setCursor(0, 0); // Primera fila
  lcd.print("Temp: ");
  lcd.print(temperatura);
  lcd.print(" C");

  lcd.setCursor(0, 1); // Segunda fila
  lcd.print("Hum: ");
  lcd.print(humedad);
  lcd.print(" %");

  // Mostrar datos del MPU6050 en el monitor serial
  Serial.print("AngleX: ");
  Serial.print(angleX);
  Serial.print(" | AngleY: ");
  Serial.println(angleY);
  enviarDatos();
  delay(1000);
  //Tiempo para actualizar los límites (Actualmente: 2 minutos)
  int tiempo_actualizar_milisegundos = 120000;
  static unsigned long lastUpdate = 0;
  if (millis() - lastUpdate > tiempo_actualizar_milisegundos) {
    obtenerLimitValues();
    lastUpdate = millis();
  }
}
