/****************************************
 * Include Libraries
 ****************************************/
#include "UbidotsEsp32Mqtt.h"
#include <Adafruit_MPU6050.h>
#include <Adafruit_Sensor.h>
#include <Wire.h>
#include <DHT.h>
//Puede que no la estemos utilizando
#include <DHT_U.h>
/****************************************
 * Define Constants
 ****************************************/
const char *UBIDOTS_TOKEN = "BBUS-RP7o82lkV4g7LCtktF9LxDhLoTESS5";  // Put here your Ubidots TOKEN
const char *WIFI_SSID = "POCO X6 Pro 5G";      // Put here your Wi-Fi SSID
const char *WIFI_PASS = "RedmiJuan";      // Put here your Wi-Fi password
const char *DEVICE_LABEL = "esp32";   // Put here your Device label to which data  will be published
const char *VARIABLE_LABEL1 = "temperatura"; // Put here your Variable label to which data  will be published
const char *VARIABLE_LABEL2 = "humedad";

const int PUBLISH_FREQUENCY = 5000; // Update rate in milliseconds

unsigned long timer;

//Variables para el sensor DHT11
#define dht11 4
#define DHTTYPE DHT11
DHT dht(dht11, DHTTYPE);
int temp;
int hum;


Ubidots ubidots(UBIDOTS_TOKEN, "Juan_Manuel_Murillo");

/****************************************
 * Auxiliar Functions
 ****************************************/

void callback(char *topic, byte *payload, unsigned int length)
{
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  for (int i = 0; i < length; i++)
  {
    Serial.print((char)payload[i]);
  }
  Serial.println();
}

/****************************************
 * Main Functions
 ****************************************/

//Leds
int led1 = 13;
int led2 = 14;
int led3 = 15;

//Push Button
int push_b = 12;

//MPU6050
Adafruit_MPU6050 mpu;

void ledParpadeante(int pin, int del){
  digitalWrite(pin, 1);
  delay(del);
  digitalWrite(pin, 0);
  delay(del);
};

void setup()
{
  // put your setup code here, to run once:
  Serial.begin(9600);
  pinMode(led1, OUTPUT);
  pinMode(led2, OUTPUT);
  pinMode(led3, OUTPUT);
  pinMode(push_b, INPUT_PULLUP);
  // ubidots.setDebug(true);  // uncomment this to make debug messages available
  ubidots.connectToWifi(WIFI_SSID, WIFI_PASS);
  ubidots.setCallback(callback);
  ubidots.setup();
  ubidots.reconnect();
  dht.begin();
  timer = millis();
  if(!mpu.begin()){
    Serial.println("Failed to find MPU6050 chip");
    while(1){
      delay(10);
    }
  }
Serial.println("MPU6050 Found!");

  mpu.setAccelerometerRange(MPU6050_RANGE_8_G);
  Serial.print("Accelerometer range set to: ");
  switch (mpu.getAccelerometerRange()) {
  case MPU6050_RANGE_2_G:
    Serial.println("+-2G");
    break;
  case MPU6050_RANGE_4_G:
    Serial.println("+-4G");
    break;
  case MPU6050_RANGE_8_G:
    Serial.println("+-8G");
    break;
  case MPU6050_RANGE_16_G:
    Serial.println("+-16G");
    break;
  }
  mpu.setGyroRange(MPU6050_RANGE_500_DEG);
  Serial.print("Gyro range set to: ");
  switch (mpu.getGyroRange()) {
  case MPU6050_RANGE_250_DEG:
    Serial.println("+- 250 deg/s");
    break;
  case MPU6050_RANGE_500_DEG:
    Serial.println("+- 500 deg/s");
    break;
  case MPU6050_RANGE_1000_DEG:
    Serial.println("+- 1000 deg/s");
    break;
  case MPU6050_RANGE_2000_DEG:
    Serial.println("+- 2000 deg/s");
    break;
  }

  mpu.setFilterBandwidth(MPU6050_BAND_5_HZ);
  Serial.print("Filter bandwidth set to: ");
  switch (mpu.getFilterBandwidth()) {
  case MPU6050_BAND_260_HZ:
    Serial.println("260 Hz");
    break;
  case MPU6050_BAND_184_HZ:
    Serial.println("184 Hz");
    break;
  case MPU6050_BAND_94_HZ:
    Serial.println("94 Hz");
    break;
  case MPU6050_BAND_44_HZ:
    Serial.println("44 Hz");
    break;
  case MPU6050_BAND_21_HZ:
    Serial.println("21 Hz");
    break;
  case MPU6050_BAND_10_HZ:
    Serial.println("10 Hz");
    break;
  case MPU6050_BAND_5_HZ:
    Serial.println("5 Hz");
    break;
  }

  Serial.println("");
  delay(100);
}
int num_led = led1;
void loop()
{
  //MPU
  sensors_event_t a, g, tem;
  mpu.getEvent(&a, &g, &tem);
  //LED
  int estado_pushB = digitalRead(push_b);
  if(estado_pushB == 0){
    if(num_led < led3){
      num_led++;
    }
    else{
      num_led = led1;
    }
  }
  ledParpadeante(num_led, 1000);
  /* Print out the values */
  Serial.print("Acceleration X: ");
  Serial.print(a.acceleration.x);
  Serial.print(", Y: ");
  Serial.print(a.acceleration.y);
  Serial.print(", Z: ");
  Serial.print(a.acceleration.z);
  Serial.println(" m/s^2");

  Serial.print("Rotation X: ");
  Serial.print(g.gyro.x);
  Serial.print(", Y: ");
  Serial.print(g.gyro.y);
  Serial.print(", Z: ");
  Serial.print(g.gyro.z);
  Serial.println(" rad/s");
  temp = dht.readTemperature();
  hum = dht.readHumidity();
  if(isnan(temp) || isnan(hum)){
    Serial.println(F("Error reading temperature & humidity!"));
  }
  else{
    Serial.print(F("\n Temperatura: "));
    Serial.print(temp);
    Serial.println(F("°C"));
    Serial.print(F("Humedad: "));
    Serial.print(hum);
  }
  // put your main code here, to run repeatedly:
  if (!ubidots.connected())
  {
    ubidots.reconnect();
  }
  if (millis() - timer > PUBLISH_FREQUENCY) // triggers the routine every 5 seconds
  {
    ubidots.add(VARIABLE_LABEL1, temp); // Insert your variable Labels and the value to be sent
    ubidots.add(VARIABLE_LABEL2, hum);
    ubidots.publish(DEVICE_LABEL);
    timer = millis();
  }
  ubidots.loop();
}
