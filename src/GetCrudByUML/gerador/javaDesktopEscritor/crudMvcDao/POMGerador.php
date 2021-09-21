<?php 


namespace GetCrudByUML\gerador\javaDesktopEscritor\crudMvcDao;

use GetCrudByUML\model\Software;

class POMGerador{
        private $software;
        private $listaDeArquivos;
        
        public static function main(Software $software){
            $gerador = new POMGerador($software);
            return $gerador->gerarCodigo();
        }
        public function __construct(Software $software){
            $this->software = $software;
        }
        
        public function gerarCodigo(){
            $this->geraPOMXML();
            return $this->listaDeArquivos;
        }
        
        public function geraPOMXML(){
            $codigo = '';
            $codigo .= '
<project xmlns="http://maven.apache.org/POM/4.0.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://maven.apache.org/POM/4.0.0 http://maven.apache.org/xsd/maven-4.0.0.xsd">
                
    <modelVersion>4.0.0</modelVersion>
    <groupId>com.'.strtolower($this->software->getNome()).'</groupId>
    <artifactId>'.strtolower($this->software->getNome()).'</artifactId>
    <packaging>jar</packaging>
    <version>1.0</version>
        
    <name>'.$this->software->getNome().'</name>
    <description>'.$this->software->getNome().'</description>
    <url>https://escritordesoftware.com.br</url>
        
    <properties>
        <project.build.sourceEncoding>UTF-8</project.build.sourceEncoding>
    </properties>
        
    <dependencies>
        <dependency>
            <groupId>postgresql</groupId>
            <artifactId>postgresql</artifactId>
            <version>8.3-606.jdbc4</version>
        </dependency>
        <dependency>
            <groupId>mysql</groupId>
            <artifactId>mysql-connector-java</artifactId>
            <version>5.0.4</version>
        </dependency>
        <dependency>
            <groupId>org.xerial</groupId>
            <artifactId>sqlite-jdbc</artifactId>
            <version>3.8.7</version>
        </dependency>
    </dependencies>
        
    <build>
        <plugins>
            <plugin>
                <groupId>org.apache.maven.plugins</groupId>
                <artifactId>maven-compiler-plugin</artifactId>
                <version>3.0</version>
                <configuration>
                    <source>1.7</source>
                    <target>1.7</target>
                </configuration>
            </plugin>
            <!-- com libs internas no jar -->
            <plugin>
                <artifactId>maven-assembly-plugin</artifactId>
                <version>2.5.3</version>
                <executions>
                    <execution>
                        <id>build-servidor</id>
                        <configuration>
                            <appendAssemblyId>false</appendAssemblyId>
                            <archive>
                                <manifest>
                                    <mainClass>br.com.escritordesoftware.'.strtolower($this->software->getNome()).'.main.Main</mainClass>
                                    <addClasspath>true</addClasspath>
                                </manifest>
                                <addMavenDescriptor>false</addMavenDescriptor>
                            </archive>
                            <descriptorRefs>
                                <descriptorRef>jar-with-dependencies</descriptorRef>
                            </descriptorRefs>
                            <finalName>${project.artifactId}</finalName>
                        </configuration>
                        <phase>package</phase>
                        <goals>
                            <goal>single</goal>
                        </goals>
                    </execution>
                </executions>
            </plugin>
        </plugins>
    </build>
    <organization>
        <name>Escritor de Software</name>
        <url>https://escritordesoftware.com.br</url>
    </organization>
</project>';
            $caminho = 'pom.xml';
            $this->listaDeArquivos[$caminho] = $codigo;
            return $codigo;
        }
    }
    
    
    
    
    
    
?>


