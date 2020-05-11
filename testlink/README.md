## [Testlink传送门](http://172.0.10.81/testlink/)
### 项目说明
项目包含 **[testlink](./)** ,**[automation](./automation)** ,**[project_management](./project_management)** </br>

### 基于Docker的项目构建
- [DockerFile](./Dockerfile)
- 维护人员: xiaojinshui@casachina.com.cn
- 构建容器: `docker build -t testlink:v1.0 .` 
- 启动容器: `docker run -d -it --name testlink -p 8080:80 testlink:v1.0` 
- 访问:`http://localhost:8080` 
